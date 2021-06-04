<?php

namespace ForkCMS\Modules\Blog\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

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
