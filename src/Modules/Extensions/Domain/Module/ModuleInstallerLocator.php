<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use InvalidArgumentException;
use RuntimeException;

final class ModuleInstallerLocator
{
    /** @var array<string, ModuleInstaller> */
    private array $moduleInstallers;

    /** @param iterable|ModuleInstaller[] $moduleInstallers */
    public function __construct(iterable $moduleInstallers, private bool $forkIsInstalled)
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
                static fn(ModuleInstaller $moduleInstaller) => $moduleInstaller::IS_REQUIRED
            )
        );
    }

    /** @return ModuleName[] */
    public function getModuleNamesForOverview(): array
    {
        return $this->moduleInstallersToModuleNames(
            array_filter(
                $this->moduleInstallers,
                static fn(ModuleInstaller $moduleInstaller) => $moduleInstaller::IS_VISIBLE_IN_OVERVIEW
            )
        );
    }

    /** @return array<string, ModuleInstaller> */
    public function getSortedInstallersForModuleNames(ModuleName ...$moduleNames): array
    {
        $moduleInstallers = array_combine(
            array_map(static fn(ModuleName $moduleName): string => $moduleName->getName(), $moduleNames),
            array_map(
                fn(ModuleName $moduleName): ModuleInstaller => $this->getModuleInstaller($moduleName),
                $moduleNames
            )
        );

        $sortedModuleInstallers = $this->getInstalledModules();
        while (count($moduleInstallers) > 0) {
            $foundMatch = false;
            foreach ($moduleInstallers as $name => $moduleInstaller) {
                if (count(array_diff_key($moduleInstaller->getModuleDependencies(), $sortedModuleInstallers)) === 0) {
                    $sortedModuleInstallers[$name] = $moduleInstaller;
                    $foundMatch = true;
                    unset($moduleInstallers[$name]);
                }
            }

            if (!$foundMatch) {
                throw new RuntimeException('Circular reference found in module dependencies');
            }
        }

        return $sortedModuleInstallers;
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

    /** @return ModuleInstaller[] */
    private function getInstalledModules(): array
    {
        if (!$this->forkIsInstalled) {
            return [];
        }

        throw new RuntimeException('Not implemented yet');
    }
}
