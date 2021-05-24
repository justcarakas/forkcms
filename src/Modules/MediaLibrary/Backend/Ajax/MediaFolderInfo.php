<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Core\Common\Exception\AjaxExitException;
use Symfony\Component\HttpFoundation\Response;

/**
 * This edit-action will get the item info using Ajax
 */
class MediaFolderInfo extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        // call parent
        parent::execute();

        // get parameters
        $id = $this->getRequest()->request->getInt('id', 0);

        if ($id === 0) {
            throw new AjaxExitException('no id provided');
        }

        // Currently always allow to be moved
        $this->output(Response::HTTP_OK, ['allow_move' => true]);
    }
}
