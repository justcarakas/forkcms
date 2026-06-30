<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use Doctrine\Common\Collections\ArrayCollection;
use ForkCMS\Core\Domain\Doctrine\CollectionHelper;
use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObject;
use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObjectInterface;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use Symfony\Component\Validator\Constraints as Assert;

/** @implements UniqueDataTransferObjectInterface<User> */
#[UniqueDataTransferObject(fields: ['email'], entityClass: User::class, message: 'err.EmailExists')]
#[UniqueDataTransferObject(fields: ['displayName'], entityClass: User::class, message: 'err.DisplayNameExists')]
abstract class UserDataTransferObject implements UniqueDataTransferObjectInterface
{
    /**
     * We have to limit the length because of the email because of unique index
     */
    #[Assert\Email(message: "err.EmailIsInvalid")]
    #[Assert\NotBlank(message: 'err.EmailIsRequired')]
    #[Assert\Length(max: 180, maxMessage: "err.EmailIsTooLong")]
    public ?string $email = null;

    #[Assert\NotCompromisedPassword(skipOnError: true)]
    #[Assert\Length(min: 12, minMessage: 'err.PasswordIsTooShort')]
    #[Assert\NotBlank(message: 'err.PasswordIsRequired', groups: ['create'])]
    public ?string $plainTextPassword = null;

    #[Assert\NotBlank(message: 'err.DisplayNameIsRequired')]
    public ?string $displayName = null;

    public bool $accessToBackend = true;

    public bool $superAdmin = false;

    protected ?User $userEntity;

    /** @var ArrayCollection<int|string,UserGroup> */
    public ArrayCollection $userGroups;

    public SettingsBag $settings;

    public function __construct(?User $userEntity = null)
    {
        $this->userEntity = $userEntity;
        $this->email = $userEntity?->email;
        $this->displayName = $userEntity?->displayName;
        // @phpstan-ignore nullsafe.neverNull
        $this->accessToBackend = $userEntity?->hasAccessToBackend() ?? true;
        // @phpstan-ignore nullsafe.neverNull
        $this->superAdmin = $userEntity?->superAdmin ?? false;
        $this->userGroups = CollectionHelper::toArrayCollection($userEntity?->userGroups);
        // @phpstan-ignore nullsafe.neverNull
        $this->settings = $userEntity?->settings ?? new SettingsBag();
    }

    #[\Override]
    final public function hasEntity(): bool
    {
        return $this->userEntity instanceof User;
    }

    #[\Override]
    final public function getEntity(): User
    {
        return $this->userEntity;
    }
}
