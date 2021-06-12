<?php

namespace ForkCMS\Modules\Pages\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class PagesInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Pages');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
