<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use Assert\Assertion;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use LogicException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\File\File;

final class Importer
{
    public function __construct(
        private ServiceLocator $importers,
        private TranslationRepository $translationRepository,
    ) {
    }

    public function import(string $path, bool $overwriteConflicts = false): ImportResult
    {
        $translationFile = new File($path);
        $importResult = new ImportResult();

        /** @var ImporterInterface $importer */
        $importer = $this->importers->get($translationFile->guessExtension());
        Assertion::implementsInterface($importer, ImporterInterface::class);

        $translations = $importer->getTranslations($translationFile);
        foreach ($translations as $translation) {
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

        return $importResult;
    }
}
