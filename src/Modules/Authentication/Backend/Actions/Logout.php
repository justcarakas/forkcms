<?php

namespace ForkCMS\Modules\Authentication\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\Action as BackendBaseAction;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;

/**
 * This is the logout-action, it will logout the current user
 */
class Logout extends BackendBaseAction
{
    public function execute(): void
    {
        parent::execute();
        BackendAuthentication::logout();

        // redirect to login-screen
        $this->redirect(BackendModel::createUrlForAction('Index', $this->getModule()));
    }
}
