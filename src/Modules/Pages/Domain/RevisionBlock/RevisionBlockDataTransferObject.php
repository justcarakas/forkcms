<?php

namespace ForkCMS\Modules\Pages\Domain\RevisionBlock;

use ForkCMS\Modules\Pages\Domain\Revision\Revision;

final class RevisionBlockDataTransferObject
{
    public ?Revision $revision;
    public ?string $position = null;
    public ?string $editorContent = null;
    public bool $isVisible = true;
    public ?int $sequence = null;
    /** @var array<string, mixed>  */
    public array $settings = [];

    public function __construct(?RevisionBlock $block)
    {
        if ($block === null) {
            return;
        }

        // dont map the revision since changes should result in a new revision
        $this->position = $block->getPosition();
        $this->editorContent = $block->getEditorContent();
        $this->isVisible = $block->isVisible();
        $this->sequence = $block->getSequence();
        $this->settings = $block->getSettings()->all();
    }
}
