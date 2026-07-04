<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Event\ContentBlockRevisionCreatedEvent;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\RevisionRepository;
use ForkCMS\Modules\ContentBlocks\Frontend\Widgets\ContentBlock as ContentBlockWidget;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleSettings;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Block\ModuleBlock;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class CreateContentBlockRevisionHandler implements CommandHandlerInterface
{
    public function __construct(
        private ContentBlockRepository $contentBlockRepository,
        private RevisionRepository $revisionRepository,
        private ModuleSettings $moduleSettings,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateContentBlockRevision $createContentBlock): void
    {
        $hasRevision = $createContentBlock->hasEntity();
        $contentBlock = $hasRevision ? $createContentBlock->getEntity()->contentBlock : new ContentBlock(
            new Block(
                ModuleBlock::fromFQCN(ContentBlockWidget::class),
                enabled: $createContentBlock->isEnabled,
                locale: $createContentBlock->locale
            ),
            $createContentBlock->locale
        );

        $revision = Revision::fromDataTransferObject($createContentBlock, $contentBlock);

        if ($hasRevision) {
            $activeRevision = $contentBlock->getActiveRevision();
            $activeRevision->archive();
            $this->contentBlockRepository->saveRevision($activeRevision);
        }

        $this->contentBlockRepository->saveRevision($revision);
        $createContentBlock->setEntity($revision);
        $contentBlock->revisions->set($revision->id, $revision);
        $this->eventDispatcher->dispatch(new ContentBlockRevisionCreatedEvent($revision));

        $maxRevisions = $this->moduleSettings->get(
            ModuleName::fromFQCN(self::class),
            Revision::SETTING_MAX_REVISIONS_NAME,
        );
        $this->revisionRepository->deleteArchivedRevisionsBeyondLimit($contentBlock, $maxRevisions);
    }
}
