<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\User\Blameable;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Backend\Domain\User\User;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Like CreatedAndUpdatedBy, but without the updatedBy/updatedOn pair: use this directly on
 * entities that are never edited in place (e.g. a revision), where "last edited" would be
 * misleading since a change always produces a new entity instead of updating the existing one.
 *
 * Carries no DataGridPropertyColumn attribute, since what to call this column can differ per
 * grid (e.g. "last edited" on an aggregate's index vs. "created" on its own revision history) -
 * add it explicitly per grid via Column::createPropertyColumn() instead.
 */
trait CreatedBy
{
    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'createdBy')]
    private(set) ?User $createdBy;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private(set) DateTimeImmutable $createdOn;
}
