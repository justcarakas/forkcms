<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\StorageProvider;

use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;

interface LiipImagineBundleStorageProviderInterface
{
    public function getWebPathWithFilter(MediaItem $mediaItem, string $liipImagineBundleFilter = null): string;
}
