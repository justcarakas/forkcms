<?php

namespace ForkCMS\Modules\MediaLibrary\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class MediaLibraryInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
