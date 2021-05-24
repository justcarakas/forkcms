<?php

namespace ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\Command;

use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\MediaGallery;
use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\MediaGalleryDataTransferObject;

final class UpdateMediaGallery extends MediaGalleryDataTransferObject
{
    public function __construct(MediaGallery $mediaGallery)
    {
        parent::__construct($mediaGallery);
    }
}
