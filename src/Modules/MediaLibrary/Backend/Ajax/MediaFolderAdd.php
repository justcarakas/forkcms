<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command\CreateMediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Exception\MediaFolderNotFound;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use ForkCMS\Core\Common\Exception\AjaxExitException;
use Symfony\Component\HttpFoundation\Response;

/**
 * This AJAX-action will add a new MediaFolder.
 */
class MediaFolderAdd extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        /** @var CreateMediaFolder $createMediaFolder */
        $createMediaFolder = $this->createMediaFolder();

        // Success message
        $this->output(
            Response::HTTP_OK,
            $createMediaFolder->getMediaFolderEntity(),
            vsprintf(
                Language::msg('FolderIsAdded'),
                [
                    $createMediaFolder->getMediaFolderEntity()->getId(),
                ]
            )
        );
    }

    private function createMediaFolder(): CreateMediaFolder
    {
        /** @var MediaFolder|null $parent */
        $parent = $this->getParent();

        /** @var string $name */
        $name = $this->getFolderName($parent);

        /** @var CreateMediaFolder $createMediaFolder */
        $createMediaFolder = new CreateMediaFolder(
            $name,
            BackendAuthentication::getUser()->getUserId(),
            $parent
        );

        // Handle the MediaFolder create
        $this->get('command_bus.public')->handle($createMediaFolder);

        return $createMediaFolder;
    }

    protected function getFolderName(MediaFolder $parent = null): string
    {
        // Define name
        $name = $this->getRequest()->request->get('name');

        // We don't have a name
        if (empty($name)) {
            throw new AjaxExitException(Language::err('NameIsRequired'));
        }

        // Folder name already exists
        if ($this->get(MediaFolderRepository::class)->existsByName($name, $parent)) {
            throw new AjaxExitException(Language::err('MediaFolderExists'));
        }

        return $name;
    }

    protected function getParent(): ?MediaFolder
    {
        // Get parameters
        $parentId = $this->getRequest()->request->getInt('parent_id');

        if ($parentId === 0) {
            return null;
        }

        try {
            return $this->get(MediaFolderRepository::class)->findOneById($parentId);
        } catch (MediaFolderNotFound $mediaFolderNotFound) {
            throw new AjaxExitException(Language::err('ParentNotExists'));
        }
    }
}
