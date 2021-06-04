<?php

namespace ForkCMS\Modules\Location\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

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
