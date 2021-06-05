<?php

namespace ForkCMS\Core\Installer\Domain\Module;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Domain\Module\ModuleInstallerLocator;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ModulesStepConfigurationHandler implements CommandHandlerInterface
{
    public function __construct(
        private SessionInterface $session,
        private ModuleInstallerLocator $moduleInstallerLocator
    ) {
    }

    public function __invoke(ModulesStepConfiguration $modulesStepConfiguration)
    {
        InstallerConfiguration::fromSession($this->session)->withModulesStep(
            $modulesStepConfiguration,
            $this->moduleInstallerLocator
        );
    }
}
