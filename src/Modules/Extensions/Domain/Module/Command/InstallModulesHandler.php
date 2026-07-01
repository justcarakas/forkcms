<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Module\Command;

use ForkCMS\Core\Domain\Kernel\Event\ClearCacheEvent;
use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Extensions\Domain\Module\Event\ModuleInstalledEvent;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstallerLocator;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class InstallModulesHandler implements CommandHandlerInterface
{
    public function __construct(
        private ModuleInstallerLocator $moduleInstallerLocator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(InstallModules $installModules): void
    {
        $moduleInstallers = $this->moduleInstallerLocator->getSortedUninstalledInstallersForModuleNames(
            ...$installModules->moduleNames
        );

        foreach ($moduleInstallers as $moduleInstaller) {
            $moduleInstaller->preInstall();
        }

        foreach ($moduleInstallers as $moduleInstaller) {
            $moduleInstaller->registerModule();
            $moduleInstaller->install();
        }

        $this->eventDispatcher->dispatch(new ModuleInstalledEvent(...$installModules->moduleNames));
        $this->eventDispatcher->dispatch(new ClearCacheEvent());
    }
}
