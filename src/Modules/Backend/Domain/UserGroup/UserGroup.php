<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Doctrine\CollectionHelper;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\AjaxAction\ModuleAjaxAction;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridActionColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridMethodColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserGroupRepository::class)]
#[ORM\Table(name: 'backend__user_group')]
#[UniqueEntity(fields: ['name'])]
#[DataGrid('UserGroup')]
#[DataGridActionColumn(
    route: 'backend',
    routeAttributes: [
        'module' => 'backend',
        'action' => 'user_group_edit'
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Edit',
    iconClass: 'edit',
    requiredRole: ModuleAction::ROLE_PREFIX . 'BACKEND__USER_GROUP_EDIT'
)]
class UserGroup
{
    use Blameable;

    public const ADMIN_GROUP_ID = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, unique: true)]
    #[DataGridPropertyColumn(sortable: true, filterable: true, label: 'lbl.Name')]
    private string $name;

    /**
     * @var Collection<int, User>|User[]
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "userGroups")]
    protected Collection $users;

    use EntityWithSettingsTrait;

    /**
     * @var array<string, string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles;

    private function __construct(string $name)
    {
        $this->name = $name;
        $this->users = new ArrayCollection();
        $this->settings = new SettingsBag();
        $this->roles = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public static function fromDataTransferObject(UserGroupDataTransferObject $userDataTransferObject): self
    {
        $userGroup = $userDataTransferObject->getEntity() ?? new self($userDataTransferObject->name);
        $userGroup->name = $userDataTransferObject->name;
        CollectionHelper::updateCollection(
            $userDataTransferObject->users,
            $userGroup->users,
            fn (User $user) => $userGroup->addUser($user),
            fn (User $user) => $userGroup->removeUser($user)
        );

        $userGroup->settings = $userDataTransferObject->settings;
        $userGroup->roles = [];
        foreach ($userDataTransferObject->actions as $action) {
            $userGroup->addAction(ModuleAction::fromFQCN($action));
        }
        foreach ($userDataTransferObject->ajaxActions as $ajaxAction) {
            $userGroup->addAjaxAxtion(ModuleAjaxAction::fromFQCN($ajaxAction));
        }
        foreach ($userDataTransferObject->widgets as $widget) {
            $userGroup->addWidget(ModuleWidget::fromFQCN($widget));
        }

        return $userGroup;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addUser(User $user): void
    {
        if ($this->users->contains($user)) {
            return;
        }

        $this->users->add($user);
        $user->addUserGroup($this);
    }

    public function removeUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            return;
        }

        $this->users->removeElement($user);
        $user->removeUserGroup($this);
    }

    /** @return Collection<int, User>|User[] */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /** @return string[] */
    public function getRoles(): array
    {
        return $this->roles;
    }

    #[DataGridMethodColumn(label: 'lbl.NumberOfUsers')]
    public function getUserCount(): int
    {
        return $this->users->count();
    }

    public static function dataGridEditLinkCallback(self $userGroup): array
    {
        return ['id' => $userGroup->getId()];
    }

    public function addModule(ModuleName $moduleName): void
    {
        $this->addRole($moduleName->asRole());
    }

    public function addAction(ModuleAction $moduleAction): void
    {
        $this->addModule($moduleAction->getModule());
        $this->addRole($moduleAction->asRole());
    }

    public function addWidget(ModuleWidget $moduleWidget): void
    {
        $this->addModule($moduleWidget->getModule());
        $this->addRole($moduleWidget->asRole());
    }

    public function addAjaxAxtion(ModuleAjaxAction $moduleAjaxAction): void
    {
        $this->addModule($moduleAjaxAction->getModule());
        $this->addRole($moduleAjaxAction->asRole());
    }

    private function addRole(string $role): void
    {
        $this->roles[$role] = $role;
    }
}
