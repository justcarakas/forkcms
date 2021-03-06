<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;

final class DeleteMediaFolderHandler
{
    /** @var MediaFolderRepository */
    protected $mediaFolderRepository;

    public function __construct(MediaFolderRepository $mediaFolderRepository)
    {
        $this->mediaFolderRepository = $mediaFolderRepository;
    }

    public function handle(DeleteMediaFolder $deleteMediaFolder): void
    {
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $deleteMediaFolder->mediaFolder;

        $this->mediaFolderRepository->remove($mediaFolder);
    }
}
