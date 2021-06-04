<?php

namespace ForkCMS\Modules\Extensions\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

final class ExtensionsInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Extensions');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
