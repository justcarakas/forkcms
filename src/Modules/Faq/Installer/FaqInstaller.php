<?php

namespace ForkCMS\Modules\Faq\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

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
