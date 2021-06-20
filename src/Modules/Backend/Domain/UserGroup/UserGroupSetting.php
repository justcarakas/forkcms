<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_group_settings")
 */
class UserGroupSetting
{
    /**
     * @ORM\Id
     * @Orm\ManyToOne(targetEntity="UserGroup", inversedBy="settings")
     * @Orm\JoinColumn(referencedColumnName="id")
     */
    private UserGroup $userGroup;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private string $key;

    /** @ORM\Column(type="object") */
    private mixed $value;

    public function __construct(UserGroup $userGroup, string $key, mixed $value)
    {
        $this->userGroup = $userGroup;
        $this->key = $key;
        $this->value = $value;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
