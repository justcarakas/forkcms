<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;

final class DeleteMediaFolder
{
    /** @var MediaFolder */
    public $mediaFolder;

    public function __construct(MediaFolder $mediaFolder)
    {
        $this->mediaFolder = $mediaFolder;
    }
}
