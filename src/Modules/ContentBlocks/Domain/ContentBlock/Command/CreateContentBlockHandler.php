<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

use ForkCMS\Core\Backend\Helper\Model;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use ForkCMS\Modules\Pages\Domain\ModuleExtra\ModuleExtraType;

final class CreateContentBlockHandler
{
    /** @var ContentBlockRepository */
    private $contentBlockRepository;

    public function __construct(ContentBlockRepository $contentBlockRepository)
    {
        $this->contentBlockRepository = $contentBlockRepository;
    }

    public function handle(CreateContentBlock $createContentBlock): void
    {
        $createContentBlock->extraId = $this->getNewExtraId();
        $createContentBlock->id = $this->contentBlockRepository->getNextIdForLanguage($createContentBlock->locale);

        $contentBlock = ContentBlock::fromDataTransferObject($createContentBlock);
        $this->contentBlockRepository->add($contentBlock);

        $createContentBlock->setContentBlockEntity($contentBlock);
    }

    private function getNewExtraId(): int
    {
        return Model::insertExtra(
            ModuleExtraType::widget(),
            'ContentBlocks',
            'Detail'
        );
    }
}
