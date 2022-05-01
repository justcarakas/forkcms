<?php

namespace ForkCMS\Modules\Pages\Domain\Revision\Command;

use ForkCMS\Core\Domain\Kernel\Event\ClearCacheEvent;
use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Pages\Domain\Revision\Event\RevisionCreatedEvent;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use ForkCMS\Modules\Pages\Domain\Revision\RevisionRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CreateRevisionHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(CreateRevision $createRevision): void
    {
        $createRevision->setEntity(Revision::fromDataTransferObject($createRevision));
        $this->revisionRepository->save($createRevision->getEntity());
        $this->eventDispatcher->dispatch(new RevisionCreatedEvent($createRevision->getEntity()));
        if ($createRevision->clearCache) {
            $this->eventDispatcher->dispatch(new ClearCacheEvent());
        }
    }
}
