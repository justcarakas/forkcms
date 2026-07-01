<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Installer\Console;

use Assert\AssertionFailedException;
use Doctrine\DBAL\DriverManager;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Core\Domain\Kernel\Kernel;
use ForkCMS\Modules\Extensions\Domain\Module\InstalledModules;
use ForkCMS\Modules\Installer\Domain\Authentication\AuthenticationStepConfiguration;
use ForkCMS\Modules\Installer\Domain\Configuration\ConfigurationParser;
use ForkCMS\Modules\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Installer\Domain\Installer\InstallerStep;
use ForkCMS\Modules\Installer\Domain\Installer\InstallForkCMS;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * This command will run the requirements checks of fork.
 */
#[AsCommand(
    name: 'forkcms:installer:install',
    description: 'Install fork from the console using the fork-cms-installation-configuration.yaml config file'
)]
class InstallCommand extends Command
{
    private InputInterface $input;
    private OutputInterface $output;
    private SymfonyStyle $formatter;

    public function __construct(
        private readonly bool $forkIsInstalled,
        private readonly ConfigurationParser $configurationParser,
        private readonly Kernel $kernel,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('email', 'u', InputOption::VALUE_REQUIRED, 'The email address of the backend user')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The password of the backend user')
            ->addOption('clear-database', 'c', InputOption::VALUE_NONE, 'Clear the database of all content before installing')
            ->setHidden($this->forkIsInstalled);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->formatter = new SymfonyStyle($input, $output);

        $installerConfiguration = $this->getInstallerConfiguration();
        if (!$installerConfiguration instanceof InstallerConfiguration) {
            return self::FAILURE;
        }
        if ($input->getOption('clear-database')) {
            if (!$this->formatter->confirm('Are you sure you want to clear all tables in this database?', false)) {
                return self::FAILURE;
            }

            $connection = DriverManager::getConnection([
                'driver' => 'pdo_mysql',
                'host' => $installerConfiguration->databaseHostname,
                'user' => $installerConfiguration->databaseUsername,
                'password' => $installerConfiguration->databasePassword,
                'dbname' => $installerConfiguration->databaseName,
                'port' => $installerConfiguration->databasePort,
                'charset' => 'utf8mb4',
            ]);
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
            $platform = $connection->getDatabasePlatform();
            foreach ($connection->executeQuery('SHOW TABLES')->fetchFirstColumn() as $table) {
                $connection->executeStatement('DROP TABLE ' . $platform->quoteSingleIdentifier($table));
            }
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        }

        InstalledModules::setModulesToInstall(...$installerConfiguration->modules);
        $this->kernel->reboot(null);
        $_SERVER['HTTPS'] = 'on';
        try {
            // We can't get this via DI because it only works after the kernel reboot
            $messengerBus = Ensure::isInstanceOf(
                $this->kernel->getContainer()->get('messenger.default_bus'),
                MessageBusInterface::class,
                'The messenger bus is missing'
            );
            $messengerBus->dispatch(new InstallForkCMS($installerConfiguration));
        } catch (Throwable $throwable) {
            if ($output->isVerbose()) {
                throw $throwable;
            }
            // There was a validation error
            $this->formatter->error($throwable->getMessage());

            return self::FAILURE;
        }

        $this->formatter->success('Fork CMS is installed');

        return self::SUCCESS;
    }

    private function serverMeetsRequirements(): bool
    {
        $checkRequirementsCommand = $this->getApplication()->find('forkcms:installer:check-requirements');
        $this->formatter->writeln('<info>Checking requirements</info>');
        $checkRequirementsResult = $checkRequirementsCommand->run(new ArrayInput([]), $this->output);

        return $checkRequirementsResult === CheckRequirementsCommand::RETURN_SERVER_MEETS_REQUIREMENTS ||
            $checkRequirementsResult === CheckRequirementsCommand::RETURN_SERVER_MEETS_REQUIREMENTS_BUT_HAS_WARNINGS;
    }

    private function getInstallerConfiguration(): ?InstallerConfiguration
    {
        if ($this->forkIsInstalled) {
            $this->formatter->error('Fork CMS is already installed');

            return null;
        }

        if (!$this->serverMeetsRequirements()) {
            $this->formatter->error('This server is not compatible with Fork CMS');

            return null;
        }

        if (!$this->configurationParser->configurationFileExists()) {
            $this->formatter->error(
                'Please add the configuration file created by a previous install named ' .
                'fork-cms-installation-configuration.yaml before running the command in the root directory.'
            );

            return null;
        }

        $installerConfiguration = $this->configurationParser->loadFromFile();
        $installerConfiguration->withRequirementsStep();

        $adminEmail = $this->input->getOption('email');
        $adminPassword = $this->input->getOption('password');

        if ($adminEmail !== null && $adminPassword !== null) {
            $authenticationStepConfiguration = AuthenticationStepConfiguration::fromInstallerConfiguration(
                $installerConfiguration
            );
            try {
                $authenticationStepConfiguration->email = Ensure::isEmail($adminEmail);
            } catch (AssertionFailedException) {
                $this->formatter->error('Please provide a valid email address.');

                return null;
            }
            $authenticationStepConfiguration->password = $adminPassword;
            $installerConfiguration->withAuthenticationStep($authenticationStepConfiguration);
        }

        $step = InstallerStep::INSTALL;

        if (!$installerConfiguration->isValidForStep($step)) {
            $this->formatter->error(
                'The installation configuration is not complete or valid.'
            );

            return null;
        }

        return $installerConfiguration;
    }
}
