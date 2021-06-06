<?php

namespace ForkCMS\Modules\Location\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class LocationInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Location');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
