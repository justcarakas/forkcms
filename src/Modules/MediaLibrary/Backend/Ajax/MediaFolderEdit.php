<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command\UpdateMediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Exception\MediaFolderNotFound;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use ForkCMS\Core\Common\Exception\AjaxExitException;
use ForkCMS\Core\Common\Uri;
use Symfony\Component\HttpFoundation\Response;

/**
 * This edit-action will update a folder using AJAX
 */
class MediaFolderEdit extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        /** @var UpdateMediaFolder $updateMediaFolder */
        $updateMediaFolder = $this->updateMediaFolder();

        // Output
        $this->output(
            Response::HTTP_OK,
            $updateMediaFolder->getMediaFolderEntity(),
            sprintf(
                Language::msg('MediaFolderIsEdited'),
                $updateMediaFolder->getMediaFolderEntity()->getName()
            )
        );
    }

    protected function getMediaFolder(): MediaFolder
    {
        $id = $this->getRequest()->request->getInt('folder_id');

        // validate values
        if ($id === 0) {
            throw new AjaxExitException(Language::err('FolderIdIsRequired'));
        }

        try {
            /** @var MediaFolder $mediaFolder */
            return $this->get(MediaFolderRepository::class)->findOneById($id);
        } catch (MediaFolderNotFound $mediaFolderNotFound) {
            throw new AjaxExitException(Language::err('MediaFolderDoesNotExists'));
        }
    }

    protected function getFolderName(): string
    {
        $name = $this->getRequest()->request->get('name');

        if (empty($name)) {
            throw new AjaxExitException(Language::err('TitleIsRequired'));
        }

        return $name;
    }

    private function updateMediaFolder(): UpdateMediaFolder
    {
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $this->getMediaFolder();

        /** @var string $name */
        $name = $this->getFolderName();

        /** @var UpdateMediaFolder $updateMediaFolder */
        $updateMediaFolder = new UpdateMediaFolder($mediaFolder);
        $updateMediaFolder->name = htmlspecialchars($name, ENT_QUOTES);

        // Handle the MediaFolder update
        $this->get('command_bus.public')->handle($updateMediaFolder);

        return $updateMediaFolder;
    }
}
