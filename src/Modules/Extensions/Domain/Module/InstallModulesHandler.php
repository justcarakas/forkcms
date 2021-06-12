<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;

final class InstallModulesHandler implements CommandHandlerInterface
{
    public function __construct(private ModuleInstallerLocator $moduleInstallerLocator)
    {
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
            $moduleInstaller->install();
            $moduleInstaller->registerModule();
        }
    }
}
