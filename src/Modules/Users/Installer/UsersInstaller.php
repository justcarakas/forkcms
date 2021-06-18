<?php

namespace ForkCMS\Modules\Users\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class UsersInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
