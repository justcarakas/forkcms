<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * This AJAX-action will get all media items for a group,
 * which was trying to be saved, but another parent error appeared.
 */
class MediaItemGetAllById extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // Output success message with variables
        $this->output(
            Response::HTTP_OK,
            [
                'items' => $this->getMediaItems(),
            ]
        );
    }

    protected function getMediaItems(): array
    {
        /** @var array $ids */
        $ids = explode(',', $this->getRequest()->request->get('media_ids'));

        // We have no ids
        if (empty($ids)) {
            return [];
        }

        return $this->get(MediaItemRepository::class)->findById($ids);
    }
}
