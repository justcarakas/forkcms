<?php

namespace ForkCMS\Modules\Backend\Installer;

use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItem;
use ForkCMS\Modules\Backend\Domain\RememberMeToken\RememberMeToken;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserSetting;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupAction;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupModule;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupSetting;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupWidget;
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
            UserGroup::class,
            UserGroupSetting::class,
            UserGroupModule::class,
            UserGroupAction::class,
            UserGroupWidget::class,
        );
    }

    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');
    }
}
