<?php

namespace ForkCMS\Modules\MediaGalleries\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

final class MediaGalleriesInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('MediaGalleries');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
