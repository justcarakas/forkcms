<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;

final class UpdateMediaFolderHandler
{
    public function handle(UpdateMediaFolder $updateMediaFolder): void
    {
        // We redefine the MediaFolder, so we can use it in an action
        $updateMediaFolder->setMediaFolderEntity(MediaFolder::fromDataTransferObject($updateMediaFolder));
    }
}
