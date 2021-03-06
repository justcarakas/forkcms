<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_settings")
 */
class UserSetting
{
    /**
     * @ORM\Id
     * @Orm\ManyToOne(targetEntity="ForkCMS\Modules\Backend\Domain\User\User", inversedBy="settings")
     * @Orm\JoinColumn(referencedColumnName="id")
     */
    private User $user;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private string $key;

    /** @ORM\Column(type="object") */
    private mixed $value;

    public function __construct(User $user, string $key, mixed $value)
    {
        $this->user = $user;
        $this->key = $key;
        $this->value = $value;
    }

    public function getUser(): User
    {
        return $this->user;
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
