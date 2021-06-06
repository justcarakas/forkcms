<?php

namespace ForkCMS\Modules\Internationalization\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class InternationalisationInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Internationalization');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
