<?php

namespace ForkCMS\Modules\Extensions\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class ExtensionsInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Extensions');
    }

    public function preInstall(): void
    {
        $this->createDatabasesForEntities(Module::class);
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
