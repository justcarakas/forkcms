<?php

namespace ForkCMS\Modules\Internationalisation\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;

final class InternationalisationInstaller extends ModuleInstaller
{
    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('Internationalisation');
    }

    public function preInstall(): void
    {
        $this->createDatabasesForEntities(Translation::class);
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
