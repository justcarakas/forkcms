<?php

namespace ForkCMS\Modules\FormBuilder\Installer;

use ForkCMS\Core\Domain\Module\ModuleInstaller;
use ForkCMS\Core\Domain\Module\ModuleName;

final class FormBuilderInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('FormBuilder');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
