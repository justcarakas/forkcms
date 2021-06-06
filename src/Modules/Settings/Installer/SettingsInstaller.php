<?php

namespace ForkCMS\Modules\Settings\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class SettingsInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Settings');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
