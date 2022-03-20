<?php

namespace ForkCMS\Modules\Frontend\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;

final class FrontendInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;

    public function preInstall(): void
    {
        $this->createTableForEntities(Meta::class, Block::class);
    }

    public function install(): void
    {
    }
}
