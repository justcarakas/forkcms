<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use RuntimeException;

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
            try {
                $moduleInstaller->install();
            } catch (RuntimeException) {
                //ignore for now while developing
            }
            $moduleInstaller->registerModule();
        }
    }
}
