<?php

namespace ForkCMS\Modules\MediaGalleries\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\Command\DeleteMediaGallery;
use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\Exception\MediaGalleryNotFound;
use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\MediaGallery;
use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\MediaGalleryRepository;

/**
 * This is the class to Delete a MediaGallery
 */
class MediaGalleryDelete extends BackendBaseActionDelete
{
    public function execute(): void
    {
        parent::execute();

        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'MediaGalleryDelete']
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(Model::createUrlForAction('MediaGalleryIndex') . '&error=something-went-wrong');
        }
        $deleteFormData = $deleteForm->getData();

        /** @var MediaGallery $mediaGallery */
        $mediaGallery = $this->getMediaGallery($deleteFormData['id']);

        /** @var DeleteMediaGallery $deleteMediaGallery */
        $deleteMediaGallery = new DeleteMediaGallery($mediaGallery);

        // Handle the MediaGallery delete
        $this->get('command_bus.public')->handle($deleteMediaGallery);

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'media-gallery-deleted',
                    'var' => $deleteMediaGallery->mediaGallery->getTitle(),
                ]
            )
        );
    }

    private function getMediaGallery(string $id): MediaGallery
    {
        try {
            /** @var MediaGallery|null $mediaGallery */
            return $this->get(MediaGalleryRepository::class)->findOneById($id);
        } catch (MediaGalleryNotFound $mediaGalleryNotFound) {
            $this->redirect(
                $this->getBackLink(
                    [
                        'error' => 'non-existing-media-gallery',
                    ]
                )
            );
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return Model::createUrlForAction(
            'MediaGalleryIndex',
            null,
            null,
            $parameters
        );
    }
}
