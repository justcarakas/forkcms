<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Event\ContentBlockDeletedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class DeleteContentBlockHandler implements CommandHandlerInterface
{
    public function __construct(
        private ContentBlockRepository $contentBlockRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(DeleteContentBlock $deleteContentBlock): void
    {
        $contentBlock = $this->contentBlockRepository->find($deleteContentBlock->id);

        if ($contentBlock === null || $this->contentBlockRepository->isContentBlockInUse($contentBlock)) {
            return;
        }

        $this->contentBlockRepository->remove($contentBlock);
        $this->eventDispatcher->dispatch(new ContentBlockDeletedEvent($contentBlock));
    }
}
