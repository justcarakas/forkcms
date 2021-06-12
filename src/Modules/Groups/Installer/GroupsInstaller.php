<?php

namespace ForkCMS\Modules\Groups\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class GroupsInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Groups');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
