<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderCache;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Invalidate MediaFolder Backend Cache Subscriber
 */
final class MediaFolderInvalidateBackendCacheSubscriber implements EventSubscriber
{
    /**
     * @var MediaFolderCache
     */
    protected $mediaFolderCache;

    public function __construct(MediaFolderCache $mediaFolderCache)
    {
        $this->mediaFolderCache = $mediaFolderCache;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    private function invalidateBackendCacheForMediaFolders(LifecycleEventArgs $eventArgs): void
    {
        if ($eventArgs->getObject() instanceof MediaFolder || $eventArgs->getObject() instanceof MediaItem) {
            $this->mediaFolderCache->delete();
        }
    }

    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $this->invalidateBackendCacheForMediaFolders($eventArgs);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs): void
    {
        $this->invalidateBackendCacheForMediaFolders($eventArgs);
    }

    public function postRemove(LifecycleEventArgs $eventArgs): void
    {
        $this->invalidateBackendCacheForMediaFolders($eventArgs);
    }
}
