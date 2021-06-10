<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Modules\Backend\Installer\BackendInstaller;
use ForkCMS\Modules\Error\Installer\ErrorInstaller;
use ForkCMS\Modules\Extensions\Installer\ExtensionsInstaller;
use ForkCMS\Modules\Internationalisation\Installer\InternationalisationInstaller;

abstract class ModuleInstaller
{
    protected bool $isVisibleInOverview = true;
    protected bool $isRequired = false;
    /** @var array<string,ModuleName> */
    private array $moduleDependencies = [];
    private ?array $defaultModuleDependencies = null;

    abstract public static function getModuleName(): ModuleName;

    /**
     * Use this method to perform actions before the uninstalled module dependencies are installed
     */
    public function preInstall(): void
    {
    }

    abstract public function install(): void;

    final public function isVisibleInOverview(): bool
    {
        return $this->isVisibleInOverview;
    }

    final public function isRequired(): bool
    {
        return $this->isRequired;
    }

    final protected function addModuleDependency(ModuleName $moduleName): void
    {
        $this->moduleDependencies[$moduleName->getName()] = $moduleName;
        $this->defaultModuleDependencies = null; // reset cache
    }

    /** @return array<string,ModuleName> */
    final public function getModuleDependencies(): array
    {
        return array_merge($this->moduleDependencies, $this->getDefaultModuleDependencies());
    }

    /** @return array<string,ModuleName> */
    private function getDefaultModuleDependencies(): array
    {
        if ($this->defaultModuleDependencies !== null) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies = [];

        $backendInstaller = BackendInstaller::getModuleName();
        if ($backendInstaller === static::getModuleName()) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies[$backendInstaller->getName()] = $backendInstaller;

        $internationalisationInstaller = InternationalisationInstaller::getModuleName();
        if ($internationalisationInstaller === static::getModuleName()) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies[$internationalisationInstaller->getName()] = $internationalisationInstaller;

        $errorInstaller = ErrorInstaller::getModuleName();
        if ($errorInstaller === static::getModuleName()) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies[$errorInstaller->getName()] = $errorInstaller;

        $extensionInstaller = ExtensionsInstaller::getModuleName();
        if ($extensionInstaller === static::getModuleName()) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies[$extensionInstaller->getName()] = $extensionInstaller;

        return $this->defaultModuleDependencies;
    }
}
