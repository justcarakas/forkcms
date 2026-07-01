<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait Blameable
{
    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'createdBy')]
    private(set) ?User $createdBy;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private(set) DateTimeImmutable $createdOn;

    #[Gedmo\Blameable]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'updatedBy')]
    // phpcs:disable -- property hooks are not yet supported
    private(set) ?User $updatedBy {
        get => $this->updatedBy ?? $this->createdBy;
    }
    // phpcs:enable

    #[Gedmo\Timestampable]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    // phpcs:disable
    private(set) DateTimeImmutable $updatedOn {
        get {
            return $this->updatedOn ?? $this->createdOn;
        }
    }
    // phpcs:enable
}
