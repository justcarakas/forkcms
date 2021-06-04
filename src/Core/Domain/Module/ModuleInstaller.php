<?php

namespace ForkCMS\Core\Domain\Module;

abstract class ModuleInstaller
{
    protected bool $isVisibleInOverview = true;
    protected bool $isRequired = false;

    abstract public function getModuleName(): ModuleName;

    abstract public function install(): void;

    final public function isVisibleInOverview(): bool
    {
        return $this->isVisibleInOverview;
    }

    final public function isRequired(): bool
    {
        return $this->isRequired;
    }
}
