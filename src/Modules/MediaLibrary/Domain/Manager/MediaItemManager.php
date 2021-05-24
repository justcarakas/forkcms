<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\Manager;

use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\DeleteMediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class MediaItemManager
{
    /** @var MediaItemRepository */
    private $mediaItemRepository;

    /** @var MessageBusSupportingMiddleware */
    private $commandBus;

    public function __construct(
        MediaItemRepository $mediaItemRepository,
        MessageBusSupportingMiddleware $commandBus
    ) {
        $this->mediaItemRepository = $mediaItemRepository;
        $this->commandBus = $commandBus;
    }

    public function delete(MediaItem $mediaItem): DeleteMediaItem
    {
        $deleteMediaItem = new DeleteMediaItem($mediaItem);

        // Handle the MediaItem delete
        $this->commandBus->handle($deleteMediaItem);

        return $deleteMediaItem;
    }
}
