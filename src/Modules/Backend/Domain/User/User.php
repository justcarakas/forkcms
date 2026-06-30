<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use ForkCMS\Core\Domain\Util\Ensure;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Doctrine\CollectionHelper;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Backend\Actions\AuthenticationLogin;
use ForkCMS\Modules\Backend\Backend\Actions\AuthenticationResetPassword;
use ForkCMS\Modules\Backend\Backend\Actions\Forbidden as ActionForbidden;
use ForkCMS\Modules\Backend\Backend\Actions\NotFound as ActionNotFound;
use ForkCMS\Modules\Backend\Backend\Ajax\Forbidden as AjaxForbidden;
use ForkCMS\Modules\Backend\Backend\Ajax\NotFound as AjaxNotFound;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use Gedmo\Mapping\Annotation as Gedmo;
use InvalidArgumentException;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridActionColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'])]
#[Gedmo\SoftDeleteable(timeAware:true)]
#[DataGrid('User')]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'backend',
        'action' => 'user-edit',
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Edit',
    class: 'btn btn-primary btn-sm',
    iconClass: 'fa fa-edit',
    requiredRole: ModuleAction::ROLE_PREFIX . 'BACKEND__USER_EDIT',
    columnAttributes: ['class' => 'fork-data-grid-action'],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Blameable;

    use EntityWithSettingsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    #[DataGridPropertyColumn(
        sortable: true,
        filterable: true,
        label: 'lbl.Email',
        route: 'backend_action',
        routeAttributes: [
            'module' => 'backend',
            'action' => 'user-edit',
        ],
        routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
        routeRole: ModuleAction::ROLE_PREFIX . 'BACKEND__USER_EDIT',
        columnAttributes: ['class' => 'title'],
    )]
    // phpcs:disable -- property hooks are not yet supported
    public string $email {
        get => $this->email;
        set(string $email) {
            $this->email = Ensure::hasMaxLength(Ensure::isEmail($email), 180);
        }
    }
    // phpcs:enable

    #[ORM\Column(type: Types::STRING, length: 255)]
    public string $password;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[DataGridPropertyColumn(sortable: true, filterable: true, label: 'lbl.DisplayName')]
    private(set) string $displayName;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $accessToBackend;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $superAdmin;

    /** @var Collection<int|string, UserGroup> */
    #[ORM\ManyToMany(targetEntity: UserGroup::class, inversedBy: 'users')]
    #[ORM\InverseJoinColumn(referencedColumnName: 'id')]
    private(set) Collection $userGroups;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private(set) ?DateTimeImmutable $deletedAt = null;

    /** @param Collection<int|string, UserGroup>|null $userGroups */
    public function __construct(
        string $email,
        private ?string $plainTextPassword,
        string $displayName,
        bool $accessToBackend,
        bool $superAdmin,
        ?Collection $userGroups = null,
        ?SettingsBag $settings = null
    ) {
        $this->email = $email;
        if ($this->plainTextPassword !== null) {
            $this->plainTextPassword = trim($this->plainTextPassword);
        }
        $this->password = '';
        $this->displayName = $displayName;
        $this->accessToBackend = $accessToBackend;
        $this->superAdmin = $superAdmin;
        $this->userGroups = $userGroups ?? new ArrayCollection();
        $this->settings = $settings ?? new SettingsBag();
    }

    public static function fromDataTransferObject(UserDataTransferObject $userDataTransferObject): self
    {
        if ($userDataTransferObject->hasEntity()) {
            $user = $userDataTransferObject->getEntity();
            $user->email = $userDataTransferObject->email ?? throw new InvalidArgumentException('Email is required');
            $user->accessToBackend = $userDataTransferObject->accessToBackend;
            $user->superAdmin = $userDataTransferObject->superAdmin;
            $user->plainTextPassword = $userDataTransferObject->plainTextPassword;
            if ($user->plainTextPassword !== null) {
                $user->plainTextPassword = trim($user->plainTextPassword);
            }
            CollectionHelper::updateCollection(
                $userDataTransferObject->userGroups,
                $user->userGroups,
                static fn (UserGroup $userGroup): UserGroup => $user->addUserGroup($userGroup),
                static fn (UserGroup $userGroup): UserGroup => $user->removeUserGroup($userGroup)
            );
            $user->displayName = $userDataTransferObject->displayName;
            $user->settings = $userDataTransferObject->settings;

            return $user;
        }

        return new self(
            $userDataTransferObject->email,
            $userDataTransferObject->plainTextPassword,
            $userDataTransferObject->displayName,
            $userDataTransferObject->accessToBackend,
            $userDataTransferObject->superAdmin,
            $userDataTransferObject->userGroups,
            $userDataTransferObject->settings
        );
    }

    /** @see UserInterface */
    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @see UserInterface */
    #[\Override]
    public function getRoles(): array
    {
        if (!$this->accessToBackend) {
            return [];
        }

        $roles = [
            'ROLE_USER',
            ActionNotFound::getActionSlug()->asModuleAction()->asRole(),
            ActionForbidden::getActionSlug()->asModuleAction()->asRole(),
            AjaxNotFound::getAjaxActionSlug()->asModuleAction()->asRole(),
            AjaxForbidden::getAjaxActionSlug()->asModuleAction()->asRole(),
            AuthenticationLogin::getActionSlug()->asModuleAction()->asRole(),
            AuthenticationResetPassword::getActionSlug()->asModuleAction()->asRole(),
        ];
        $groupRoles = [];

        foreach ($this->userGroups as $userGroup) {
            $groupRoles[] = array_values($userGroup->roles);
        }

        return array_unique(array_merge($roles, ...$groupRoles));
    }

    /** @see UserInterface */
    #[\Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    public function hashPassword(UserPasswordHasherInterface $passwordHasher): void
    {
        if ($this->plainTextPassword === null || $this->plainTextPassword === '') {
            return;
        }

        if ($this->password !== '') {
            $this->settings->set('last_password_change', time());
        }

        $this->password = $passwordHasher->hashPassword($this, $this->plainTextPassword);
        $this->plainTextPassword = null;
    }

    public function hasAccessToBackend(): bool
    {
        return $this->accessToBackend;
    }

    /** @TODO implement */
    public function undoDelete(): void
    {
        $this->deletedAt = null;
    }

    public function addUserGroup(UserGroup $userGroup): UserGroup
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
            $userGroup->addUser($this);
        }

        return $userGroup;
    }

    public function removeUserGroup(UserGroup $userGroup): UserGroup
    {
        if ($this->userGroups->contains($userGroup)) {
            $this->userGroups->removeElement($userGroup);
            $userGroup->removeUser($this);
        }

        return $userGroup;
    }

    /**
     * @param array{string?: string} $attributes
     *
     * @return array{string?: int|string}
     */
    public static function dataGridEditLinkCallback(self $user, array $attributes): array
    {
        $attributes['slug'] = $user->id;

        return $attributes;
    }

    public function registerAuthenticationFailure(): void
    {
        $this->settings->set('last_authentication_failure', time());
    }

    public function registerAuthenticationSuccess(): void
    {
        $this->settings->set('last_authentication_success', time());
    }
}
