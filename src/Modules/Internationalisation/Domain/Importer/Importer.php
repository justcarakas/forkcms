<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use Assert\Assertion;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final class Importer
{
    public function __construct(
        private ServiceLocator $importers,
        private string $cacheDir,
        private TranslationRepository $translationRepository,
        private InstalledLocaleRepository $installedLocaleRepository,
        private ModuleRepository $moduleRepository,
    ) {
    }

    public function import(string $path, bool $overwriteConflicts = false, ?Locale $specificLocale = null): ImportResult
    {
        $translationFile = new File($path);
        $importResult = new ImportResult();

        /** @var ImporterInterface $importer */
        $importer = $this->importers->get($translationFile->guessExtension());
        Assertion::implementsInterface($importer, ImporterInterface::class);

        $translations = $importer->getTranslations($translationFile);
        $locales = $this->installedLocaleRepository->findAllIndexed();
        $modules = $this->moduleRepository->findAllIndexed();
        $fallbackLocale = Locale::fallback()->value;
        foreach ($translations as $translation) {
            $application = $translation->getDomain()->getApplication();
            $moduleName = $translation->getDomain()->getModuleName();
            $locale = $translation->getLocale()->value;
            if (($moduleName instanceof ModuleName && !array_key_exists($moduleName->getName(), $modules))
                || ($specificLocale !== null && !$specificLocale->equals($translation->getLocale()))
                || (
                    $locale !== $fallbackLocale
                    && (
                        !array_key_exists($locale, $locales)
                        || ($application->equals(Application::frontend()) && !$locales[$locale]->isEnabledForWebsite())
                        || ($application->equals(Application::backend()) && !$locales[$locale]->isEnabledForUser())
                    )
                )
            ) {
                $importResult->addSkipped($translation);
                continue;
            }

            try {
                $this->translationRepository->save($translation);
                $importResult->addImported($translation);
            } catch (UniqueConstraintViolationException) {
                $existingTranslation = $this->translationRepository->find($translation->getId());
                if ($overwriteConflicts && $existingTranslation !== null) {
                    $existingTranslation->change($translation->getValue());
                    $this->translationRepository->save($existingTranslation);
                    $importResult->addUpdated($existingTranslation);
                    continue;
                }
                $importResult->addFailed($translation);
            }
        }

        $filesystem = new Filesystem();
        $translationsDirectory = $this->cacheDir . '/translations';
        if ($filesystem->exists($translationsDirectory)) {
            $filesystem->remove($translationsDirectory);
        }

        return $importResult;
    }
}
