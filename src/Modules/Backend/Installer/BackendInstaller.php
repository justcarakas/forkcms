<?php

namespace ForkCMS\Modules\Backend\Installer;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Backend\Backend\Actions\UserGroupAdd;
use ForkCMS\Modules\Backend\Backend\Actions\UserGroupDelete;
use ForkCMS\Modules\Backend\Backend\Actions\UserGroupEdit;
use ForkCMS\Modules\Backend\Backend\Actions\UserGroupIndex;
use ForkCMS\Modules\Backend\Domain\Authentication\RememberMeToken;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItem;
use ForkCMS\Modules\Backend\Domain\User\Command\CreateUser;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class BackendInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;

    public function preInstall(): void
    {
        $this->createDatabasesForEntities(
            NavigationItem::class,
            User::class,
            RememberMeToken::class,
            UserGroup::class,
        );
        $installerConfiguration = InstallerConfiguration::fromCache();

        $createUser = new CreateUser();
        $createUser->email = $installerConfiguration->getAdminEmail();
        $createUser->plainTextPassword = $installerConfiguration->getAdminPassword();
        $createUser->superAdmin = true;
        $createUser->accessToBackend = true;
        $createUser->userGroups->add($this->userGroupRepository->getAdminUserGroup());

        /** @var User $user */
        $user = $this->dispatchCommand($createUser)->last(HandledStamp::class)->getResult();

        // Authenticate the created user
        $this->tokenStorage->setToken(
            $this->authenticationManager->authenticate(
                new UsernamePasswordToken($user, 'backend', $user->getRoles())
            )
        );
    }

    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');

        $this->getOrCreateBackendNavigationItem(
            TranslationKey::label('Groups'),
            UserGroupIndex::getActionSlug(),
            $this->getSettingsNavigationItem(),
            [
                UserGroupAdd::getActionSlug(),
                UserGroupEdit::getActionSlug(),
                UserGroupDelete::getActionSlug(),
            ],
        );
    }
}
