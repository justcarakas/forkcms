<?php

namespace ForkCMS\Modules\Groups\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

final class GroupsInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Groups');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
