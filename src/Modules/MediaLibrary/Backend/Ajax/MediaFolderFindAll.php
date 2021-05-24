<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use Symfony\Component\HttpFoundation\Response;

/**
 * This AJAX-action will get all media folders.
 */
class MediaFolderFindAll extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // Output success message with MediaFolder items
        $this->output(Response::HTTP_OK, $this->get('media_library.cache.media_folder')->get());
    }
}
