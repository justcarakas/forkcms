<?php

namespace ForkCMS\Modules\Tags\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\Action as BackendBaseAction;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Tags\Backend\Helper\Model as BackendTagsModel;

/**
 * This action is used to perform mass actions on tags (delete, ...)
 */
class MassAction extends BackendBaseAction
{
    public function execute(): void
    {
        parent::execute();

        $this->checkToken();

        // action to execute
        $action = $this->getRequest()->query->get('action');
        if (!in_array($action, ['delete'])) {
            $this->redirect(BackendModel::createUrlForAction('Index') . '&error=no-action-selected');
        }

        // no id's provided
        if (!$this->getRequest()->query->has('id')) {
            $this->redirect(
                BackendModel::createUrlForAction('Index') . '&error=no-selection'
            );
        } else {
            // at least one id
            // redefine id's
            $aIds = (array) $this->getRequest()->query->get('id');

            // delete comment(s)
            if ($action === 'delete') {
                BackendTagsModel::delete($aIds);
            }
        }

        // redirect
        $this->redirect(
            BackendModel::createUrlForAction('Index') . '&report=deleted'
        );
    }
}
