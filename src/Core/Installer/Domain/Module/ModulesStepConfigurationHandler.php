<?php

namespace ForkCMS\Core\Installer\Domain\Module;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstallerLocator;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ModulesStepConfigurationHandler implements CommandHandlerInterface
{
    public function __construct(
        private SessionInterface $session,
        private ModuleInstallerLocator $moduleInstallerLocator,
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(ModulesStepConfiguration $modulesStepConfiguration)
    {
        InstallerConfiguration::fromSession($this->session)->withModulesStep(
            $modulesStepConfiguration,
            $this->moduleInstallerLocator,
            $this->commandBus
        );
    }
}
