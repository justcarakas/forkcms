<?php

namespace ForkCMS\Modules\Settings\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class SettingsInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
