<?php

namespace ForkCMS\Modules\Pages\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class PagesInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
