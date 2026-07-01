<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Console;

use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Importer\Importer;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

#[AsCommand(
    name: 'forkcms:internationalisation:locale:import',
    description: 'Import fork translations for a specific module or from a given file'
)]
class ImportLocaleCommand extends Command
{
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $rootDir,
        private readonly Importer $translationImporter,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite the existing translations')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Path to the file with the translations')
            ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'Name of the module that contains the translations')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Only install for a specific locale');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getOption('file');
        $moduleName = $input->getOption('module');
        $locale = $input->getOption('locale');
        $overwriteExistingTranslations = $input->getOption('overwrite');
        $formatter = new SymfonyStyle($input, $output);

        if ($filePath === null && $moduleName === null) {
            $formatter->error('Please specify a module or path to a translation file');

            return self::INVALID;
        }

        $translationPath = $this->getTranslationPath($filePath, $moduleName);

        try {
            $importResults = $this->translationImporter->import(
                $translationPath,
                $overwriteExistingTranslations,
                $locale === null ? null : Locale::from($locale)
            );
        } catch (FileNotFoundException) {
            $formatter->error('The given locale file (' . $translationPath . ') does not exist.');

            return self::INVALID;
        }

        if ($importResults->getTotalCount() === 0) {
            $formatter->error('No translations were found.');

            return self::INVALID;
        }

        if ($importResults->importedCount > 0) {
            $formatter->comment('Imported ' . $importResults->importedCount . ' translations succesfully!');
        }
        if ($importResults->updatedCount > 0) {
            $formatter->comment('Updated ' . $importResults->updatedCount . ' translations succesfully!');
        }
        if ($importResults->skippedCount > 0) {
            $formatter->comment(
                sprintf(
                    'Skipped %d translations because they belong to a locale that is not installed ' .
                    'or to a different locale than specified with the --locale option.',
                    $importResults->skippedCount
                )
            );
        }
        if ($importResults->getFailedCount() > 0) {
            $formatter->warning(
                sprintf(
                    'Failed to import %d translations because they already existed, ' .
                    'add --overwrite if you want to overwrite them.',
                    $importResults->getFailedCount()
                )
            );
        }

        return $importResults->getFailedCount() > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function getTranslationPath(?string $filePath, ?string $moduleName): string
    {
        return $filePath ?? sprintf(
            '%s/src/Modules/%s/assets/installer/translations.xml',
            $this->rootDir,
            ModuleName::fromString(
                Ensure::isNotNull($moduleName, 'If you do not pass a filePath you need to pass a moduleName')
            )
        );
    }
}
