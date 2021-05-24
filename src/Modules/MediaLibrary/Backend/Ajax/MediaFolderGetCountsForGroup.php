<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use ForkCMS\Modules\MediaLibrary\Domain\MediaGroup\Exception\MediaGroupNotFound;
use ForkCMS\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use ForkCMS\Modules\MediaLibrary\Domain\MediaGroup\MediaGroupRepository;
use ForkCMS\Core\Common\Exception\AjaxExitException;
use Symfony\Component\HttpFoundation\Response;

/**
 * This AJAX-action will get the counts for every folder in a group.
 */
class MediaFolderGetCountsForGroup extends BackendBaseAJAXAction
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        /** @var MediaGroup|null $mediaGroup */
        $mediaGroup = $this->getMediaGroup();

        // Output success message
        $this->output(
            Response::HTTP_OK,
            $mediaGroup instanceof MediaGroup
                ? $this->get(MediaFolderRepository::class)->getCountsForMediaGroup($mediaGroup) : []
        );
    }

    private function getMediaGroup(): ?MediaGroup
    {
        $id = $this->getRequest()->request->get('group_id');

        // GroupId not valid
        if ($id === null) {
            throw new AjaxExitException(Language::err('GroupIdIsRequired'));
        }

        try {
            return $this->get(MediaGroupRepository::class)->findOneById($id);
        } catch (MediaGroupNotFound $mediaGroupNotFound) {
            return null;
        }
    }
}
