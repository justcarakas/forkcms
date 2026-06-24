<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use Assert\Assertion;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Event\TranslationChangedEvent;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Event\TranslationCreatedEvent;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Importer
{
    /** @param ServiceLocator<ImporterInterface> $importers */
    public function __construct(
        private readonly ServiceLocator $importers,
        private readonly string $cacheDir,
        private readonly TranslationRepository $translationRepository,
        private readonly InstalledLocaleRepository $installedLocaleRepository,
        private readonly ModuleRepository $moduleRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator
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

        /** @var ImporterInterface $importer */
        $importer = $this->importers->get($translationFile->guessExtension());
        Assertion::implementsInterface($importer, ImporterInterface::class);

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

            if ($translation->getDomain()->getApplication() === Application::INSTALLER) {
                ModuleInstaller::addInstallerTranslation(
                    $translation->getLocale()->value,
                    $translation->getDomain()->getDomain(),
                    (string) $translation->getKey(),
                    $translation->getValue()
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
     * @param array<string, mixed> $locales
     * @param array<string, mixed> $modules
     */
    private function shouldSkipTranslation(
        Translation $translation,
        array $locales,
        array $modules,
        string $fallbackLocale,
        ?Locale $specificLocale,
    ): bool {
        $application = $translation->getDomain()->getApplication();
        $moduleName = $translation->getDomain()->getModuleName();
        $locale = $translation->getLocale()->value;

        if ($moduleName instanceof ModuleName && !array_key_exists($moduleName->getName(), $modules)) {
            return true;
        }

        if ($specificLocale !== null && $specificLocale !== $translation->getLocale()) {
            return true;
        }

        if ($locale === $fallbackLocale) {
            return false;
        }

        return !array_key_exists($locale, $locales)
            || ($application === Application::FRONTEND && !$locales[$locale]->isEnabledForWebsite())
            || ($application === Application::BACKEND && !$locales[$locale]->isEnabledForUser());
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
        $existingTranslation = $existingTranslations[$translation->getId()]
            ?? $newTranslations[$translation->getId()]
            ?? $this->translationRepository->find($translation->getId());

        if ($existingTranslation !== null) {
            if ($overwriteConflicts) {
                $existingTranslation->change($translation->getValue());
                $existingTranslations[$translation->getId()] = $existingTranslation;
                $importResult->addUpdated();

                return;
            }

            $importResult->addFailed($translation);

            return;
        }

        $newTranslations[$translation->getId()] = $translation;
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
