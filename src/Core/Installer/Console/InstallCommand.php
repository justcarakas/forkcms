<?php

namespace ForkCMS\Core\Installer\Console;

use Assert\Assertion;
use Assert\AssertionFailedException;
use ForkCMS\Core\Domain\Kernel\Kernel;
use ForkCMS\Core\Installer\Domain\Authentication\AuthenticationStepConfiguration;
use ForkCMS\Core\Installer\Domain\Configuration\ConfigurationParser;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStep;
use ForkCMS\Core\Installer\Domain\Installer\InstallForkCMS;
use ForkCMS\Modules\Extensions\Domain\Module\InstalledModules;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * This command will run the requirements checks of fork
 */
class InstallCommand extends Command
{
    private InputInterface $input;
    private OutputInterface $output;
    private SymfonyStyle $formatter;

    public function __construct(
        private bool $forkIsInstalled,
        private ConfigurationParser $configurationParser,
        private Kernel $kernel,
    ) {
        parent::__construct('forkcms:installer:install');
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Install fork from the console using the configuration in the fork-cms-installation-configuration.yaml file'
            )
            ->addOption('email', 'u', InputOption::VALUE_REQUIRED, 'The email address of the backend user')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The password of the backend user')
            ->setHidden($this->forkIsInstalled);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->formatter = new SymfonyStyle($input, $output);

        $installerConfiguration = $this->getInstallerConfiguration();
        if (!$installerConfiguration instanceof InstallerConfiguration) {
            return self::FAILURE;
        }

        InstalledModules::setModulesToInstall(...$installerConfiguration->getModules());
        $this->kernel->reboot(null);
        $_SERVER['HTTPS'] = 'on';
        try {
            $this->kernel->getContainer()->get('messenger.default_bus')->dispatch(new InstallForkCMS($installerConfiguration));
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

    private function serverMeetsRequirements(): int
    {
        $checkRequirementsCommand = $this->getApplication()->find('forkcms:installer:check-requirements');
        $this->formatter->writeln('<info>Checking requirements</info>');
        $checkRequirementsResult = $checkRequirementsCommand->run(new ArrayInput([]), $this->output);

        return $checkRequirementsResult === CheckRequirementsCommand::RETURN_SERVER_MEETS_REQUIREMENTS
               || $checkRequirementsResult === CheckRequirementsCommand::RETURN_SERVER_MEETS_REQUIREMENTS_BUT_HAS_WARNINGS;
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
                'Please add the configuration file created by a previous install named fork-cms-installation-configuration.yaml before running the command in the root directory.'
            );

            return null;
        }

        $installerConfiguration = InstallerConfiguration::fromSession(new Session());
        $this->configurationParser->loadFromFile($installerConfiguration);
        $installerConfiguration->withRequirementsStep();

        $adminEmail = $this->input->getOption('email');
        $adminPassword = $this->input->getOption('password');

        if ($adminEmail !== null && $adminPassword !== null) {
            try {
                Assertion::email($adminEmail);
            } catch (AssertionFailedException) {
                $this->formatter->error('Please provide a valid email address.');

                return null;
            }

            $authenticationStepConfiguration = AuthenticationStepConfiguration::fromInstallerConfiguration(
                $installerConfiguration
            );
            $authenticationStepConfiguration->email = $adminEmail;
            $authenticationStepConfiguration->password = $adminPassword;
            $installerConfiguration->withAuthenticationStep($authenticationStepConfiguration);
        }

        $step = InstallerStep::install();

        if (!$installerConfiguration->isValidForStep($step)) {
            $this->formatter->error(
                'The installation configuration is not complete or valid.'
            );

            return null;
        }

        return $installerConfiguration;
    }
}
