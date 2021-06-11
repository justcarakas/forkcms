<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

final class InstallModules
{
    private array $moduleNames;

    public function __construct(ModuleName ...$moduleNames)
    {
        $this->moduleNames = $moduleNames;
    }

    public function getModuleNames(): array
    {
        return $this->moduleNames;
    }
}
