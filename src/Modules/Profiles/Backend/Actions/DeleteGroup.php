<?php

namespace ForkCMS\Modules\Profiles\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Profiles\Backend\Helper\Model as BackendProfilesModel;

/**
 * This action will delete a profile group.
 */
class DeleteGroup extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteGroup']
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction(
                'Groups',
                null,
                null,
                ['error' => 'something-went-wrong']
            ));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $this->id = (int) $deleteFormData['id'];

        // does the item exist
        if ($this->id === 0 || !BackendProfilesModel::existsGroup($this->id)) {
            $this->redirect(BackendModel::createUrlForAction('Groups', null, null, ['error' => 'non-existing']));

            return;
        }

        parent::execute();

        $group = BackendProfilesModel::getGroup($this->id);

        BackendProfilesModel::deleteGroup($this->id);

        $this->redirect(BackendModel::createUrlForAction(
            'Groups',
            null,
            null,
            ['report' => 'deleted', 'var' => $group['name']]
        ));
    }
}
