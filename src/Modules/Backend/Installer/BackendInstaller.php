<?php

namespace ForkCMS\Modules\Backend\Installer;

use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItem;
use ForkCMS\Modules\Backend\Domain\RememberMeToken\RememberMeToken;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\UserSetting\UserSetting;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class BackendInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;

    public function preInstall(): void
    {
        $this->createDatabasesForEntities(
            NavigationItem::class,
            User::class,
            UserSetting::class,
            RememberMeToken::class,
        );
    }

    public function install(): void
    {
    }
}
