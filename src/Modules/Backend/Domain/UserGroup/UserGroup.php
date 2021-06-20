<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UserGroupRepository")
 * @ORM\Table(name="user_groups")
 */
#[UniqueEntity(fields: ['name'])]
class UserGroup
{
    public const ADMIN_GROUP_ID = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private string $name;

    /**
     * @var Collection&User[]
     *
     * @ORM\ManyToMany(targetEntity="ForkCMS\Modules\Backend\Domain\User\User", mappedBy="userGroups")
     */
    protected $users;

    /**
     * @var Collection&UserGroupSetting[]
     *
     * @Orm\OneToMany(
     *     targetEntity="UserGroupSetting",
     *     mappedBy="userGroup",
     *     indexBy="key",
     *     cascade={"persist", "remove"}
     * )
     */
    private Collection $settings;

    /**
     * @var Collection&UserGroupModule[]
     *
     * @Orm\OneToMany(
     *     targetEntity="UserGroupModule",
     *     mappedBy="userGroup",
     *     cascade={"persist", "remove"}
     * )
     */
    private Collection $modules;

    /**
     * @var Collection&UserGroupAction[]
     *
     * @Orm\OneToMany(
     *     targetEntity="UserGroupAction",
     *     mappedBy="userGroup",
     *     cascade={"persist", "remove"}
     * )
     */
    private Collection $actions;

    /**
     * @var Collection&UserGroupWidget[]
     *
     * @Orm\OneToMany(
     *     targetEntity="UserGroupWidget",
     *     mappedBy="userGroup",
     *     cascade={"persist", "remove"}
     * )
     */
    private Collection $widgets;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->users = new ArrayCollection();
        $this->settings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    /** @return Collection&User[] */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /** @return Collection&UserGroupSetting[] */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function setSetting(string $key, mixed $value): void
    {
        if ($this->settings->containsKey($key)) {
            $this->settings[$key]->setValue($value);

            return;
        }

        $this->settings->set($key, new UserGroupSetting($this, $key, $value));
    }

    public function removeSetting(string $key): void
    {
        if (!$this->settings->containsKey($key)) {
            return;
        }

        $this->settings->remove($key);
    }

    /** @return Collection&UserGroupModule[] */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(ModuleName $moduleName): void
    {
        if ($this->getUserGroupModuleForModuleName($moduleName) instanceof UserGroupModule) {
            return;
        }

        $this->modules->add(new UserGroupModule($this, $moduleName));
    }

    public function removeModule(ModuleName $moduleName): void
    {
        $userGroupModule = $this->getUserGroupModuleForModuleName($moduleName);

        if ($userGroupModule === null) {
            return;
        }

        $this->modules->removeElement($userGroupModule);
    }

    private function getUserGroupModuleForModuleName(ModuleName $moduleName): ?UserGroupModule
    {
        $userGroupModule = $this->modules->filter(
            static fn(UserGroupModule $userGroupModule) => $userGroupModule->getModuleName() === $moduleName
        )->first();

        return $userGroupModule instanceof UserGroupModule ? $userGroupModule : null;
    }

    /** @return Collection&UserGroupAction[] */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(ModuleAction $moduleAction): void
    {
        if ($this->getUserGroupActionForModuleAction($moduleAction) instanceof UserGroupAction) {
            return;
        }

        $this->actions->add(new UserGroupAction($this, $moduleAction));
    }

    public function removeAction(ModuleAction $moduleAction): void
    {
        $userGroupAction = $this->getUserGroupActionForModuleAction($moduleAction);

        if ($userGroupAction === null) {
            return;
        }

        $this->actions->removeElement($userGroupAction);
    }

    private function getUserGroupActionForModuleAction(ModuleAction $moduleAction): ?UserGroupAction
    {
        $userGroupAction = $this->actions->filter(
            static fn(UserGroupAction $userGroupAction) => $userGroupAction->getModuleAction() === $moduleAction
        )->first();

        return $userGroupAction instanceof UserGroupAction ? $userGroupAction : null;
    }

    /** @return Collection&UserGroupWidget[] */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    public function addWidget(ModuleWidget $moduleWidget): void
    {
        if ($this->getUserGroupWidgetForModuleWidget($moduleWidget) instanceof UserGroupWidget) {
            return;
        }

        $this->widgets->add(new UserGroupWidget($this, $moduleWidget));
    }

    public function removeWidget(ModuleWidget $moduleWidget): void
    {
        $userGroupWidget = $this->getUserGroupWidgetForModuleWidget($moduleWidget);

        if ($userGroupWidget === null) {
            return;
        }

        $this->widgets->removeElement($userGroupWidget);
    }

    private function getUserGroupWidgetForModuleWidget(ModuleWidget $moduleWidget): ?UserGroupWidget
    {
        $userGroupWidget = $this->widgets->filter(
            static fn(UserGroupWidget $userGroupWidget) => $userGroupWidget->getModuleWidget() === $moduleWidget
        )->first();

        return $userGroupWidget instanceof UserGroupWidget ? $userGroupWidget : null;
    }
}
