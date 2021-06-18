<?php

namespace ForkCMS\Core\Console\Install;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
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

    public function __construct(
        private string $rootDir,
        private Connection $dbalConnection,
    ) {
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
        $this->clearCache($io);

        return $returnCode;
    }

    private function clearDatabase(SymfonyStyle $io): int
    {
        if (!$io->confirm('Clear the database?')) {
            return self::RETURN_DID_NOT_CLEAR_DATABASE;
        }

        $command = $this->getApplication()->find('doctrine:schema:drop');
        $command->run(
            new ArrayInput(
                [
                    '--full-database' => true,
                    '--force' => true,
                ]
            ),
            new BufferedOutput(),
        );

        $io->success('Removed all tables');

        return self::RETURN_SUCCESS;
    }

    private function removeConfiguration(SymfonyStyle $io): void
    {
        $fullPath = realpath($this->rootDir . '/.env.local');
        if (file_exists($fullPath)) {
            unlink($fullPath);
            $io->success('Removed configuration file');
        }
    }

    private function clearCache(SymfonyStyle $io): void
    {
        $command = $this->getApplication()->find('forkcms:cache:clear');
        $command->run(
            new ArrayInput([]),
            new BufferedOutput(),
        );

        $io->success('Ready for reinstall.');
    }
}
