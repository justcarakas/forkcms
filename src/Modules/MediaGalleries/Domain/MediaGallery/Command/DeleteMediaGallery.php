<?php

namespace ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\Command;

use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\MediaGallery;

final class DeleteMediaGallery
{
    /**
     * @var MediaGallery
     */
    public $mediaGallery;

    /**
     * @var bool
     */
    public $deleteAllMediaItems;

    public function __construct(MediaGallery $mediaGallery, bool $deleteAllMediaItems = false)
    {
        $this->mediaGallery = $mediaGallery;
        $this->deleteAllMediaItems = $deleteAllMediaItems;
    }
}
