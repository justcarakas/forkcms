<?php

namespace ForkCMS\Modules\Profiles\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

final class ProfilesInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Profiles');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
