<?php

namespace ForkCMS\Modules\Users\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\Action as BackendBaseAction;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Users\Backend\Helper\User as BackendUser;
use ForkCMS\Modules\Users\Backend\Helper\Model as BackendUsersModel;

/**
 * This is the undo-delete-action, it will restore a deleted user
 */
class UndoDelete extends BackendBaseAction
{
    public function execute(): void
    {
        $this->checkToken();

        $email = $this->getRequest()->query->get('email', '');

        // does the user exist
        if ($email !== '') {
            parent::execute();

            // delete item
            if (BackendUsersModel::undoDelete($email)) {
                // get user
                $user = new BackendUser(null, $email);

                // item was deleted, so redirect
                $this->redirect(
                    BackendModel::createUrlForAction('edit') . '&id=' . $user->getUserId(
                    ) . '&report=restored&var=' . $user->getSetting('nickname') . '&highlight=row-' . $user->getUserId()
                );
            } else {
                // invalid user
                $this->redirect(BackendModel::createUrlForAction('index') . '&error=non-existing');
            }
        } else {
            $this->redirect(BackendModel::createUrlForAction('index') . '&error=non-existing');
        }
    }
}
