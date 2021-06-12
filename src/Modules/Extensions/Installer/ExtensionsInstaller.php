<?php

namespace ForkCMS\Modules\Extensions\Installer;

use ForkCMS\Core\Domain\Doctrine\CreateSchema;
use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository;

final class ExtensionsInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Extensions');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
