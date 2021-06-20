<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Backend\Domain\User\User;
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
}
