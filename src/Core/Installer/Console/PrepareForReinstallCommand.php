<?php

namespace ForkCMS\Bundle\InstallerBundle\Console;

use SpoonDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command will prepare everything for a full reinstall
 */
class PrepareForReinstallCommand extends Command
{
    public const RETURN_SUCCESS = 0;
    public const RETURN_DID_NOT_REINSTALL = 1;
    public const RETURN_DID_NOT_CLEAR_DATABASE = 2;

    private SpoonDatabase $database;

    public function __construct(SpoonDatabase $database)
    {
        $this->database = $database;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('forkcms:install:prepare-for-reinstall')
            ->setDescription('Revert Fork CMS to an uninstalled state, prompting the install wizard.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$io->confirm('Are you sure you want to reinstall?')) {
            return self::RETURN_DID_NOT_REINSTALL;
        }

        $returnCode = $this->clearDatabase($io);
        $this->removeConfiguration($io);
        $this->clearCache($output, $io);

        return $returnCode;
    }

    private function clearDatabase(SymfonyStyle $io): int
    {
        if (!$io->confirm('Clear the database?')) {
            return self::RETURN_DID_NOT_CLEAR_DATABASE;
        }

        $tables = $this->database->getColumn(
            'SHOW TABLES'
        );

        if (!empty($tables)) {
            $this->database->execute('SET FOREIGN_KEY_CHECKS=0');
            $this->database->drop($tables);
        }

        $io->success('Removed all tables');

        return self::RETURN_SUCCESS;
    }

    private function removeConfiguration(SymfonyStyle $io): void
    {
        $fullPath = realpath(__DIR__ . '/../../../../..' . '/config/parameters.yaml');
        if (file_exists($fullPath)) {
            unlink($fullPath);
            $io->success('Removed configuration file');
        }
    }

    private function clearCache(OutputInterface $output, SymfonyStyle $io): void
    {
        $command = $this->getApplication()->find('forkcms:cache:clear');
        $command->run(
            new ArrayInput(
                [
                    'forkcms:cache:clear',
                ]
            ),
            $output
        );

        $io->success('Ready for reinstall.');
    }
}
