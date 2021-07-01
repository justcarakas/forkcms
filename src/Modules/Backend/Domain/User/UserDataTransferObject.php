<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

abstract class UserDataTransferObject
{
    /**
     * @Assert\Email(message="err.EmailIsInvalid")
     * @Assert\NotBlank (message="err.EmailIsRequired")
     * @Assert\Length(max=180, maxMessage="err.EmailIsTooLong")
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank(message="err.EmailIsRequired", groups={"create"})
     * @Assert\Length(message="err.PasswordIsTooShort", min=12, groups={"create"})
     */
    public ?string $plainTextPassword = null;

    public bool $accessToBackend = true;

    public bool $superAdmin = false;

    protected ?User $userEntity;

    public Collection $userGroups;

    public function __construct(?User $userEntity = null)
    {
        $this->userEntity = $userEntity;
        $this->email = $userEntity?->getEmail();
        $this->accessToBackend = $userEntity?->hasAccessToBackend() ?? true;
        $this->superAdmin = $userEntity?->isSuperAdmin() ?? false;
        $this->userGroups = $userEntity?->getUserGroups() ?? new ArrayCollection();
    }

    final public function hasEntity(): bool
    {
        return $this->userEntity instanceof User;
    }

    final public function getEntity(): User
    {
        return $this->userEntity;
    }
}
