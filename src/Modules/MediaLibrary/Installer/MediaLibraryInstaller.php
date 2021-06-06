<?php

namespace ForkCMS\Modules\MediaLibrary\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class MediaLibraryInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('MediaLibrary');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
