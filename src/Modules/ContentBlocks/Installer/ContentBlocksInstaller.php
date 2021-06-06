<?php

namespace ForkCMS\Modules\ContentBlocks\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class ContentBlocksInstaller extends ModuleInstaller
{
    public function __construct()
    {
        $this->isRequired = true;
    }

    public static function getModuleName(): ModuleName
    {
        return ModuleName::fromString('ContentBlocks');
    }

    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
