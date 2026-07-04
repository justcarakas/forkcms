<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Backend\Domain\User\Blameable\CreatedBy;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Internationalisation\Domain\Locale\EntityWithLocaleTrait;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;

#[ORM\Entity(repositoryClass: ContentBlockRepository::class)]
class ContentBlock
{
    use CreatedBy;
    use EntityWithLocaleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\ManyToOne(targetEntity: Block::class, cascade: ['persist'], fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Block $widget;

    /** @var Collection<array-key, Revision> */
    #[ORM\OneToMany(targetEntity: Revision::class, mappedBy: 'contentBlock', cascade: ['remove'], indexBy: 'id')]
    private(set) Collection $revisions;

    public function __construct(Block $widget, Locale $locale)
    {
        $this->widget = $widget;
        $this->locale = $locale;
        $this->revisions = new ArrayCollection();
    }

    public function isWidgetVisible(): bool
    {
        return $this->widget->enabled;
    }

    public function getActiveRevision(): Revision
    {
        return Ensure::isInstanceOf(
            $this->revisions->filter(static fn (Revision $revision): bool => $revision->archivedOn === null)->first(),
            Revision::class
        );
    }
}
