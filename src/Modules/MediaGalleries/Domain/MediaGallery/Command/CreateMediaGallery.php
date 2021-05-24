<?php

namespace ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\Command;

use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\MediaGalleryDataTransferObject;
use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\Status;
use ForkCMS\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use ForkCMS\Modules\MediaLibrary\Domain\MediaGroup\Type as MediaGroupType;

final class CreateMediaGallery extends MediaGalleryDataTransferObject
{
    public function __construct(int $userId, MediaGroupType $mediaGroupType)
    {
        parent::__construct();

        $this->userId = $userId;
        $this->status = Status::active();
        $this->mediaGroup = MediaGroup::create($mediaGroupType);
    }
}
