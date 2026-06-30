<?php

namespace ForkCMS\Modules\Pages\Domain\RevisionBlock;

use Doctrine\DBAL\Types\Types;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
class RevisionBlock
{
    use EntityWithSettingsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Revision::class, inversedBy: 'blocks')]
    #[ORM\JoinColumn(name: 'revision_id', nullable: false, onDelete: 'CASCADE')]
    private(set) Revision $revision;

    #[Gedmo\SortableGroup]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $position;

    #[ORM\ManyToOne(targetEntity: Block::class, fetch: 'EAGER')]
    private(set) ?Block $block;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private(set) ?string $editorContent;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $isVisible;

    #[Gedmo\SortablePosition]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $sequence;

    public function __construct(
        Revision $revision,
        string $position,
        ?Block $block = null,
        ?string $editorContent = null,
        bool $isVisible = true,
        SettingsBag $settings = new SettingsBag(),
        ?int $sequence = null,
    ) {
        $this->revision = $revision;
        $this->position = $position;
        $this->block = $block;
        $this->editorContent = $editorContent;
        $this->isVisible = $isVisible;
        $this->sequence = $sequence ?? 0;
        $this->settings = $settings;
    }

    public static function fromDataTransferObject(
        RevisionBlockDataTransferObject $revisionBlockDataTransferObject
    ): self {
        return new self(
            Ensure::isNotNull($revisionBlockDataTransferObject->revision),
            Ensure::isNotNull($revisionBlockDataTransferObject->position),
            $revisionBlockDataTransferObject->block,
            $revisionBlockDataTransferObject->editorContent,
            $revisionBlockDataTransferObject->isVisible,
            new SettingsBag($revisionBlockDataTransferObject->settings),
            $revisionBlockDataTransferObject->sequence
        );
    }

    public function getSettings(): SettingsBag
    {
        return $this->settings;
    }

    public function removeBlock(): void
    {
        $this->block = null;
    }
}
