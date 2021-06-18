<?php

namespace ForkCMS\Modules\Tags\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class TagsInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
