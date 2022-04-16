<?php

namespace ForkCMS\Modules\Extensions\Domain\Module\Command;

use ForkCMS\Core\Domain\Kernel\Command\ClearContainerCache;
use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Extensions\Domain\Module\Event\ModuleInstalledEvent;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstallerLocator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class InstallModulesHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ModuleInstallerLocator $moduleInstallerLocator,
        private readonly MessageBusInterface $commandBus,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(InstallModules $installModules): void
    {
        $moduleInstallers = $this->moduleInstallerLocator->getSortedUninstalledInstallersForModuleNames(
            ...$installModules->getModuleNames()
        );

        foreach ($moduleInstallers as $moduleInstaller) {
            $moduleInstaller->preInstall();
        }

        foreach ($moduleInstallers as $moduleInstaller) {
            $moduleInstaller->registerModule();
            $moduleInstaller->install();
        }

        $this->eventDispatcher->dispatch(new ModuleInstalledEvent(...$installModules->getModuleNames()));
    }
}
