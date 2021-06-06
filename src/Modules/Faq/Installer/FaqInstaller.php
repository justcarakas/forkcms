<?php

namespace ForkCMS\Modules\Faq\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class FaqInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Faq');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
