<?php

namespace ForkCMS\Modules\Blog\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class BlogInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Blog');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
