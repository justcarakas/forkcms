<?php

namespace ForkCMS\Modules\Internationalisation\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;

final class InternationalisationInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;

    public function preInstall(): void
    {
        $this->createDatabasesForEntities(Translation::class, InstalledLocale::class);
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
