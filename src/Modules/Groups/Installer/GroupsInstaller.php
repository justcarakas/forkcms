<?php

namespace ForkCMS\Modules\Groups\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class GroupsInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
