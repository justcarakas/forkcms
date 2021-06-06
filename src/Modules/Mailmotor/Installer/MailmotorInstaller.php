<?php

namespace ForkCMS\Modules\Mailmotor\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class MailmotorInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Mailmotor');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
