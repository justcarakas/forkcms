<?php

namespace ForkCMS\Modules\Profiles\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Profiles\Backend\Helper\Model as BackendProfilesModel;

/**
 * This action will delete a membership of a profile in a group.
 */
class DeleteProfileGroup extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteProfileGroup']
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'something-went-wrong']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $this->id = (int) $deleteFormData['id'];

        // does the item exist
        if ($this->id === 0 || !BackendProfilesModel::existsProfileGroup($this->id)) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }

        parent::execute();

        $profileGroup = BackendProfilesModel::getProfileGroup($this->id);

        BackendProfilesModel::deleteProfileGroup($this->id);

        $this->redirect(BackendModel::createUrlForAction(
            'Edit',
            null,
            null,
            ['id' => $profileGroup['profile_id'], 'report' => 'membership-deleted']
        ) . '#tabGroups');
    }
}
