<?php

namespace ForkCMS\Modules\Backend\Installer;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Backend\Backend\Actions\GroupAdd;
use ForkCMS\Modules\Backend\Backend\Actions\GroupDelete;
use ForkCMS\Modules\Backend\Backend\Actions\GroupEdit;
use ForkCMS\Modules\Backend\Backend\Actions\GroupIndex;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItem;
use ForkCMS\Modules\Backend\Domain\Authentication\RememberMeToken;
use ForkCMS\Modules\Backend\Domain\User\Command\CreateUser;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserSetting;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupAction;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupModule;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupSetting;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupWidget;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;

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
        $installerConfiguration = InstallerConfiguration::fromSession($this->session);

        $createUser = new CreateUser();
        $createUser->email = $installerConfiguration->getAdminEmail();
        $createUser->plainTextPassword = $installerConfiguration->getAdminPassword();
        $createUser->superAdmin = true;
        $createUser->accessToBackend = true;
        $createUser->userGroups->add($this->userGroupRepository->getAdminUserGroup());

        $this->dispatchCommand($createUser);
    }

    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');

        $this->getOrCreateBackendNavigationItem(
            TranslationKey::label('Groups'),
            GroupIndex::getActionSlug(),
            $this->getSettingsNavigationItem(),
            [
                GroupAdd::getActionSlug(),
                GroupEdit::getActionSlug(),
                GroupDelete::getActionSlug(),
            ],
        );
    }
}
