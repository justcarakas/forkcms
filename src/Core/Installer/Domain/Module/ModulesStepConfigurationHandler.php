<?php

namespace ForkCMS\Core\Installer\Domain\Module;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstallerLocator;
use Symfony\Component\Messenger\MessageBusInterface;

final class ModulesStepConfigurationHandler implements CommandHandlerInterface
{
    public function __construct(
        private ModuleInstallerLocator $moduleInstallerLocator,
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(ModulesStepConfiguration $modulesStepConfiguration): void
    {
        InstallerConfiguration::toCache(
            InstallerConfiguration::fromCache()->withModulesStep(
                $modulesStepConfiguration,
                $this->moduleInstallerLocator,
                $this->commandBus
            )
        );
    }
}
