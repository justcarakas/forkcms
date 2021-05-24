<?php

namespace ForkCMS\Modules\Users\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Users\Backend\Helper\User as BackendUser;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Users\Backend\Helper\Model as BackendUsersModel;

/**
 * This is the delete-action, it will deactivate and mark the user as deleted
 */
class Delete extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule()]
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'something-went-wrong']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $this->id = (int) $deleteFormData['id'];

        // does the user exist
        if ($this->id === 0
            || !BackendUsersModel::exists($this->id)
            || BackendAuthentication::getUser()->getUserId() === $this->id
        ) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }

        parent::execute();

        $user = new BackendUser($this->id);

        // God-users can't be deleted
        if ($user->isGod()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'cant-delete-god']));

            return;
        }

        BackendUsersModel::delete($this->id);

        $this->redirect(BackendModel::createUrlForAction(
            'Index',
            null,
            null,
            ['report' => 'deleted', 'var' => $user->getSetting('nickname')]
        ));
    }
}
