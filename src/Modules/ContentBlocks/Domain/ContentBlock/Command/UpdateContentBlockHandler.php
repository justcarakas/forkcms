<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;

final class UpdateContentBlockHandler
{
    /** @var ContentBlockRepository */
    private $contentBlockRepository;

    public function __construct(ContentBlockRepository $contentBlockRepository)
    {
        $this->contentBlockRepository = $contentBlockRepository;
    }

    public function handle(UpdateContentBlock $updateContentBlock): void
    {
        $contentBlock = ContentBlock::fromDataTransferObject($updateContentBlock);
        $this->contentBlockRepository->add($contentBlock);

        $updateContentBlock->setContentBlockEntity($contentBlock);
    }
}
