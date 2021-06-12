<?php

namespace ForkCMS\Modules\Users\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class UsersInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Users');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
