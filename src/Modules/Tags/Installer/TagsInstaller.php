<?php

namespace ForkCMS\Modules\Tags\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class TagsInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Tags');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
