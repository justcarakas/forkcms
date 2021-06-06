<?php

namespace ForkCMS\Modules\Authentication\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class AuthenticationInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isVisibleInOverview = false;
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Authentication');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
