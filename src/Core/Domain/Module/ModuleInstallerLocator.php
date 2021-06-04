<?php

namespace ForkCMS\Core\Domain\Module;

use InvalidArgumentException;

final class ModuleInstallerLocator
{
    /** @var array<string, ModuleInstaller> */
    private array $moduleInstallers;

    /** @param iterable|ModuleInstaller[] $moduleInstallers */
    public function __construct(iterable $moduleInstallers)
    {
        $this->moduleInstallers = [];
        foreach ($moduleInstallers as $moduleInstaller) {
            $this->moduleInstallers[$moduleInstaller::getModuleName()->getName()] = $moduleInstaller;
        }
    }

    public function getModuleInstaller(ModuleName $moduleName): ModuleInstaller
    {
        return $this->moduleInstallers[$moduleName->getName()]
               ?? throw new InvalidArgumentException('No installer was found for the module: ' . $moduleName);
    }

    /** @return ModuleName[] */
    public function getAllModuleNames(): array
    {
        return $this->moduleInstallersToModuleNames($this->moduleInstallers);
    }

    /** @return ModuleName[] */
    public function getRequiredModuleNames(): array
    {
        return $this->moduleInstallersToModuleNames(
            array_filter(
                $this->moduleInstallers,
                static fn(ModuleInstaller $moduleInstaller) => $moduleInstaller->isRequired()
            )
        );
    }

    /** @return ModuleName[] */
    public function getModuleNamesForOverview(): array
    {
        return $this->moduleInstallersToModuleNames(
            array_filter(
                $this->moduleInstallers,
                static fn(ModuleInstaller $moduleInstaller) => $moduleInstaller->isVisibleInOverview()
            )
        );
    }

    /**
     * @param ModuleInstaller[] $moduleInstallers
     * @return ModuleName[]
     */
    private function moduleInstallersToModuleNames(array $moduleInstallers): array
    {
        return array_map(
            static fn(ModuleInstaller $moduleInstaller): ModuleName => $moduleInstaller::getModuleName(),
            $moduleInstallers
        );
    }
}
