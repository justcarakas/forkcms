<?php

namespace ForkCMS\Modules\Authentication\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class AuthenticationInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
