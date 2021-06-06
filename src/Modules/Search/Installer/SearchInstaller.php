<?php

namespace ForkCMS\Modules\Search\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class SearchInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Search');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
