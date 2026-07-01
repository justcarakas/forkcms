<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Module;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class ModuleInstallerLocator
{
    /** @var array<string, ModuleInstaller> */
    private array $moduleInstallers;

    /** @param iterable|ModuleInstaller[] $moduleInstallers */
    public function __construct(
        #[AutowireIterator('forkcms.module.installer')]
        iterable $moduleInstallers,
        private readonly bool $forkIsInstalled,
        private readonly ModuleRepository $moduleRepository
    ) {
        $this->moduleInstallers = [];
        foreach ($moduleInstallers as $moduleInstaller) {
            $this->moduleInstallers[$moduleInstaller::getModuleName()->name] = $moduleInstaller;
        }
    }

    public function getModuleInstaller(ModuleName $moduleName): ModuleInstaller
    {
        return $this->moduleInstallers[$moduleName->name]
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
                static fn (ModuleInstaller $moduleInstaller) => $moduleInstaller::IS_REQUIRED
            )
        );
    }

    /** @return ModuleName[] */
    public function getModuleNamesForOverview(): array
    {
        return $this->moduleInstallersToModuleNames($this->getModuleInstallersForOverview());
    }

    /** @return ModuleInstaller[] */
    public function getModuleInstallersForOverview(): array
    {
        return array_filter(
            $this->moduleInstallers,
            static fn (ModuleInstaller $moduleInstaller) => $moduleInstaller::IS_VISIBLE_IN_OVERVIEW
        );
    }

    /** @return array<string, ModuleInstaller> */
    public function getSortedUninstalledInstallersForModuleNames(ModuleName ...$moduleNames): array
    {
        $moduleInstallers = array_combine(
            array_map(static fn (ModuleName $moduleName): string => $moduleName->name, $moduleNames),
            array_map(
                fn (ModuleName $moduleName): ModuleInstaller => $this->getModuleInstaller($moduleName),
                $moduleNames
            )
        );

        $requiredModules = [];
        /** @var ModuleInstaller $moduleInstaller */
        foreach ($moduleInstallers as $moduleInstaller) {
            foreach ($moduleInstaller->getModuleDependencies() as $moduleDependency) {
                $requiredModules[$moduleDependency->name] = $this->getModuleInstaller($moduleDependency);
            }
            $requiredModules[$moduleInstaller::getModuleName()->name] = $moduleInstaller;
        }
        $sortedModuleInstallers = [];
        while (count($requiredModules) > 0) {
            $foundMatch = false;
            foreach ($requiredModules as $name => $moduleInstaller) {
                if (count(array_diff_key($moduleInstaller->getModuleDependencies(), $sortedModuleInstallers)) === 0) {
                    $sortedModuleInstallers[$name] = $moduleInstaller;
                    $foundMatch = true;
                    unset($requiredModules[$name]);
                }
            }

            if (!$foundMatch) {
                throw new RuntimeException('Circular reference found in module dependencies');
            }
        }

        return array_diff_key($sortedModuleInstallers, $this->getInstalledModules());
    }

    /**
     * @param ModuleInstaller[] $moduleInstallers
     *
     * @return ModuleName[]
     */
    private function moduleInstallersToModuleNames(array $moduleInstallers): array
    {
        return array_map(
            static fn (ModuleInstaller $moduleInstaller): ModuleName => $moduleInstaller::getModuleName(),
            $moduleInstallers
        );
    }

    /** @return ModuleInstaller[] */
    private function getInstalledModules(): array
    {
        if (!$this->forkIsInstalled) {
            return [];
        }

        return array_map(
            fn (Module $module) => $this->getModuleInstaller($module->name),
            $this->moduleRepository->findAllIndexed()
        );
    }

    /** @return ModuleName[] */
    public static function moduleNamesFromFileSystem(): array
    {
        return array_map(
            static fn (string $moduleName): ModuleName => ModuleName::fromString(basename($moduleName)),
            glob(__DIR__ . '/../../../*', GLOB_ONLYDIR)
        );
    }
}
