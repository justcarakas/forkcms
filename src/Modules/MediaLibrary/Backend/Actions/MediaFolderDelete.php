<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command\DeleteMediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Exception\MediaFolderNotFound;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;

class MediaFolderDelete extends BackendBaseActionDelete
{
    public function execute(): void
    {
        parent::execute();

        $this->abortWhenLastFolder();

        /** @var DeleteMediaFolder $deleteMediaFolder */
        $deleteMediaFolder = $this->deleteMediaFolder();

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'media-folder-deleted',
                    'var' => urlencode($deleteMediaFolder->mediaFolder->getName()),
                ]
            )
        );
    }

    private function abortWhenLastFolder(): void
    {
        // If this is the last folder, delete is not possible
        if (count($this->get(MediaFolderRepository::class)->findAll()) === 1) {
            $this->redirect(
                $this->getBackLink(
                    [
                        'error' => 'media-folder-delete-not-possible',
                    ]
                )
            );
        }
    }

    private function checkIfDeleteIsAllowed(MediaFolder $mediaFolder): void
    {
        // If folder has children/items, delete is not possible
        if ($mediaFolder->hasConnectedItems() && $mediaFolder->hasChildrenWithConnectedItems()) {
            $this->redirect(
                $this->getBackLink(
                    [
                        'error' => 'media-folder-delete-not-possible-because-of-connected-media-items',
                    ]
                )
            );
        }

        if ($mediaFolder->hasChildren()) {
            $this->redirect(
                $this->getBackLink(
                    [
                        'error' => 'media-folder-delete-not-possible',
                    ]
                )
            );
        }
    }

    private function deleteMediaFolder(): DeleteMediaFolder
    {
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $this->getMediaFolder();

        $this->checkIfDeleteIsAllowed($mediaFolder);

        /** @var DeleteMediaFolder $deleteMediaFolder */
        $deleteMediaFolder = new DeleteMediaFolder($mediaFolder);

        // Handle the MediaFolder delete
        $this->get('command_bus.public')->handle($deleteMediaFolder);

        return $deleteMediaFolder;
    }

    private function getMediaFolder(): MediaFolder
    {
        try {
            /** @var MediaFolder */
            return $this->get(MediaFolderRepository::class)->findOneById(
                $this->getRequest()->query->getInt('id')
            );
        } catch (MediaFolderNotFound $mediaFolderNotFound) {
            $this->redirect(
                $this->getBackLink(
                    [
                        'error' => 'non-existing',
                    ]
                )
            );
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return Model::createUrlForAction(
            'MediaItemIndex',
            null,
            null,
            $parameters
        );
    }
}
