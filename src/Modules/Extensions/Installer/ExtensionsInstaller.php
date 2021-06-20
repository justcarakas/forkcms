<?php

namespace ForkCMS\Modules\Extensions\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSetting;

final class ExtensionsInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function preInstall(): void
    {
        $this->createDatabasesForEntities(
            Module::class,
            ModuleSetting::class,
        );
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
