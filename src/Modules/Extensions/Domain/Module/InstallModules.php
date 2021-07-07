<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

final class InstallModules
{
    /** @var ModuleName[] */
    private array $moduleNames;

    public function __construct(ModuleName ...$moduleNames)
    {
        $this->moduleNames = $moduleNames;
    }

    /** @return ModuleName[] */
    public function getModuleNames(): array
    {
        return $this->moduleNames;
    }
}
