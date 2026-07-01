<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Event\TranslationChangedEvent;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Event\TranslationCreatedEvent;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class Importer
{
    /** @param ServiceLocator<ImporterInterface> $importers */
    public function __construct(
        #[AutowireLocator(ImporterInterface::class)]
        private ServiceLocator $importers,
        #[Autowire(param: 'kernel.cache_dir')]
        private string $cacheDir,
        private TranslationRepository $translationRepository,
        private InstalledLocaleRepository $installedLocaleRepository,
        private ModuleRepository $moduleRepository,
        private EventDispatcherInterface $eventDispatcher,
        private TranslatorInterface $translator
    ) {
    }

    public function import(
        string|UploadedFile|File $translationFile,
        bool $overwriteConflicts = false,
        ?Locale $specificLocale = null,
    ): ImportResult {
        if (is_string($translationFile)) {
            $translationFile = new File($translationFile);
        }

        $importer = Ensure::isImplementingInterface(
            $this->importers->get(
                Ensure::isNotNull($translationFile->guessExtension(), 'Filename extension not found')
            ),
            ImporterInterface::class
        );

        $importResult = new ImportResult();
        $locales = $this->installedLocaleRepository->findAllIndexed();
        $modules = $this->moduleRepository->findAllIndexed();
        $fallbackLocale = Locale::fallback()->value;
        $existingTranslations = [];
        $newTranslations = [];

        foreach ($importer->getTranslations($translationFile) as $translation) {
            if ($this->shouldSkipTranslation($translation, $locales, $modules, $fallbackLocale, $specificLocale)) {
                $importResult->addSkipped();
                continue;
            }

            if ($translation->domain->application === Application::INSTALLER) {
                ModuleInstaller::addInstallerTranslation(
                    $translation->locale->value,
                    $translation->domain->getDomain(),
                    (string) $translation->key,
                    $translation->value
                );
                $importResult->addImported();
                continue;
            }

            $this->categorizeTranslation(
                $translation,
                $overwriteConflicts,
                $existingTranslations,
                $newTranslations,
                $importResult
            );
        }

        $this->persistTranslations($existingTranslations, $newTranslations);

        return $importResult;
    }

    /**
     * @param array<string, InstalledLocale> $locales
     * @param array<string, Module> $modules
     */
    private function shouldSkipTranslation(
        Translation $translation,
        array $locales,
        array $modules,
        string $fallbackLocale,
        ?Locale $specificLocale,
    ): bool {
        $application = $translation->domain->application;
        $moduleName = $translation->domain->moduleName;
        $locale = $translation->locale->value;

        if ($moduleName instanceof ModuleName && !array_key_exists($moduleName->name, $modules)) {
            return true;
        }

        if ($specificLocale !== null && $specificLocale !== $translation->locale) {
            return true;
        }

        if ($locale === $fallbackLocale) {
            return false;
        }

        return !array_key_exists($locale, $locales)
            || ($application === Application::FRONTEND && !$locales[$locale]->isEnabledForWebsite)
            || ($application === Application::BACKEND && !$locales[$locale]->isEnabledForUser);
    }

    /**
     * @param array<string, Translation> $existingTranslations
     * @param array<string, Translation> $newTranslations
     */
    private function categorizeTranslation(
        Translation $translation,
        bool $overwriteConflicts,
        array &$existingTranslations,
        array &$newTranslations,
        ImportResult $importResult,
    ): void {
        $existingTranslation = $existingTranslations[$translation->id]
            ?? $newTranslations[$translation->id]
            ?? $this->translationRepository->find($translation->id);

        if ($existingTranslation !== null) {
            if ($overwriteConflicts) {
                $existingTranslation->change($translation->value);
                $existingTranslations[$translation->id] = $existingTranslation;
                $importResult->addUpdated();

                return;
            }

            $importResult->addFailed($translation);

            return;
        }

        $newTranslations[$translation->id] = $translation;
        $importResult->addImported();
    }

    /**
     * @param array<string, Translation> $existingTranslations
     * @param array<string, Translation> $newTranslations
     */
    private function persistTranslations(array $existingTranslations, array $newTranslations): void
    {
        $this->translationRepository->save(...$existingTranslations, ...$newTranslations);
        $this->eventDispatcher->dispatch(new TranslationChangedEvent(...$existingTranslations));
        $this->eventDispatcher->dispatch(new TranslationCreatedEvent(...$newTranslations));

        $filesystem = new Filesystem();
        $translationsDirectory = $this->cacheDir . '/translations';
        if ($filesystem->exists($translationsDirectory)) {
            $filesystem->remove($translationsDirectory);
        }

        if ($this->translator instanceof Translator) {
            $this->translator->setFallbackLocales($this->translator->getFallbackLocales());
        }
    }

    /** @return string[] */
    public function getAvailableExtensions(): array
    {
        return array_keys($this->importers->getProvidedServices());
    }
}
