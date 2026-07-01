<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Pages\Domain\Revision\Event;

use ForkCMS\Modules\Frontend\Domain\Block\Event\BeforeDeleteBlockEvent;
use ForkCMS\Modules\Frontend\Domain\Block\Event\IsBlockInUseEvent;
use ForkCMS\Modules\Pages\Domain\Revision\RevisionRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class FrontendBlockEventListener
{
    public function __construct(private RevisionRepository $revisionRepository)
    {
    }

    #[AsEventListener(event: IsBlockInUseEvent::class)]
    public function isBlockInUse(IsBlockInUseEvent $findBlockUsagesEvent): void
    {
        if (count($this->revisionRepository->findRevisionsForFrontendBlock($findBlockUsagesEvent->block)) > 0) {
            $findBlockUsagesEvent->registerUsage();
        }
    }
    #[AsEventListener(event: BeforeDeleteBlockEvent::class)]
    public function onBlockDelete(BeforeDeleteBlockEvent $beforeDeleteBlockEvent): void
    {
        $this->revisionRepository->deleteFrontendBlockFromRevisions($beforeDeleteBlockEvent->block);
    }
}
