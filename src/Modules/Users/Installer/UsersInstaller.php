<?php

namespace ForkCMS\Modules\Users\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

final class UsersInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Users');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
