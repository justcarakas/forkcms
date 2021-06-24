<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Kernel\Command\ClearContainerCache;
use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;

final class InstallModulesHandler implements CommandHandlerInterface
{
    public function __construct(
        private ModuleInstallerLocator $moduleInstallerLocator,
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(InstallModules $installModules): void
    {
        $moduleInstallers = $this->moduleInstallerLocator->getSortedInstallersForModuleNames(
            ...$installModules->getModuleNames()
        );

        foreach ($moduleInstallers as $moduleInstaller) {
            $moduleInstaller->preInstall();
        }

        foreach ($moduleInstallers as $moduleInstaller) {
            $moduleInstaller->registerModule();
            try {
                $moduleInstaller->install();
            } catch (RuntimeException) {
                //ignore for now while developing
            }
        }

        $this->commandBus->dispatch(new ClearContainerCache());
    }
}
