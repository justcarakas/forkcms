<?php

namespace ForkCMS\Modules\Search\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class SearchInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
