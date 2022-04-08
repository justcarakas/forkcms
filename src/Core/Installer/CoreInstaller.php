<?php

namespace ForkCMS\Core\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class CoreInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;
}
