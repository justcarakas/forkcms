<?php

namespace ForkCMS\Modules\Error\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class ErrorInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Error');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
