<?php

namespace ForkCMS\Modules\Extensions\Domain\Module\Command;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final readonly class InstallModules
{
    /** @var ModuleName[] */
    public array $moduleNames;

    public function __construct(ModuleName ...$moduleNames)
    {
        $this->moduleNames = $moduleNames;
    }
}
