<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use Assert\Assertion;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Backend\Actions\AuthenticationLogin;
use ForkCMS\Modules\Backend\Backend\Actions\AuthenticationResetPassword;
use ForkCMS\Modules\Backend\Backend\Actions\NotFound;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use Gedmo\Mapping\Annotation as Gedmo;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 * @Gedmo\SoftDeleteable(timeAware=true)
 */
#[UniqueEntity(fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $accessToBackend;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $superAdmin;

    /**
     * @ORM\Column(type="core__settings__settings_bag")
     */
    private SettingsBag $settings;

    /**
     * @var Collection<int, UserGroup>|UserGroup[]
     *
     * @ORM\ManyToMany(targetEntity="ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup", inversedBy="users")
     * @ORM\JoinTable(
     *  name="users_have_user_groups",
     *  joinColumns={
     *      @ORM\JoinColumn(referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(referencedColumnName="id")
     *  }
     * )
     */
    private Collection $userGroups;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private DateTimeImmutable|null $deletedAt = null;

    /** @param Collection<int, UserGroup>|UserGroup[] $userGroups */
    public function __construct(
        string $email,
        private ?string $plainTextPassword,
        bool $accessToBackend,
        bool $superAdmin,
        Collection $userGroups = null,
    ) {
        $this->setEmail($email);
        $this->plainTextPassword = trim($this->plainTextPassword);
        $this->password = '';
        $this->accessToBackend = $accessToBackend;
        $this->superAdmin = $superAdmin;
        $this->settings = new SettingsBag();
        $this->userGroups = $userGroups ?? new ArrayCollection();
    }

    public static function createFromDataTransferObject(UserDataTransferObject $userDataTransferObject): self
    {
        if ($userDataTransferObject->hasEntity()) {
            $user = $userDataTransferObject->getEntity();
            $user->setEmail($userDataTransferObject->email ?? throw new InvalidArgumentException('Email is required'));
            $user->accessToBackend = $userDataTransferObject->accessToBackend;
            $user->superAdmin = $userDataTransferObject->superAdmin;
            $user->plainTextPassword = trim($userDataTransferObject->plainTextPassword);
            $user->userGroups = $userDataTransferObject->userGroups;

            return $user;
        }

        return new self(
            $userDataTransferObject->email,
            $userDataTransferObject->plainTextPassword,
            $userDataTransferObject->accessToBackend,
            $userDataTransferObject->superAdmin,
            $userDataTransferObject->userGroups
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        Assertion::email($email);
        Assertion::maxLength($email, 180);
        $this->email = $email;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        if (!$this->accessToBackend) {
            return [];
        }

        $roles = [
            'ROLE_USER',
            NotFound::getActionSlug()->asModuleAction()->asRole(),
            AuthenticationLogin::getActionSlug()->asModuleAction()->asRole(),
            AuthenticationResetPassword::getActionSlug()->asModuleAction()->asRole(),
        ];
        $groupRoles = [];

        foreach ($this->userGroups as $userGroup) {
            $groupRoles[] = array_values($userGroup->getRoles());
        }

        return array_unique(array_merge($roles, ...$groupRoles));
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null; // we don't need a salt because we use a modern hashing algorithm
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainTextPassword = null;
    }

    public function hashPassword(UserPasswordHasherInterface $passwordHasher): void
    {
        if ($this->plainTextPassword === null || $this->plainTextPassword === '') {
            return;
        }

        $this->password = $passwordHasher->hashPassword($this, $this->plainTextPassword);
        $this->eraseCredentials();
    }

    public function hasAccessToBackend(): bool
    {
        return $this->accessToBackend;
    }

    public function undoDelete(): void
    {
        $this->deletedAt = null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }

    public function getSettings(): SettingsBag
    {
        return $this->settings;
    }

    /** @return Collection<int, UserGroup>|UserGroup[] */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): void
    {
        if ($this->userGroups->contains($userGroup)) {
            return;
        }

        $this->userGroups->add($userGroup);
        $userGroup->addUser($this);
    }

    public function removeUserGroup(UserGroup $userGroup): void
    {
        if (!$this->userGroups->contains($userGroup)) {
            return;
        }

        $this->userGroups->removeElement($userGroup);
        $userGroup->removeUser($this);
    }
}
