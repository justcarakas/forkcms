<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait Blameable
{
    /**
     * @Gedmo\Blameable(on="create")
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    private User|null $createdBy;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $createdOn;

    /**
     * @Gedmo\Blameable(on="update")
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    private User|null $updatedBy;

    #[Gedmo\Timestampable]
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $updatedOn;

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getCreatedOn(): DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function getUpdatedOn(): DateTimeImmutable
    {
        return $this->updatedOn;
    }
}
