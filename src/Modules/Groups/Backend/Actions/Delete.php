<?php

namespace ForkCMS\Modules\Groups\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Groups\Backend\Helper\Model as BackendGroupsModel;

/**
 * This is the delete-action, it will delete an item.
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

        $this->id = $deleteFormData['id'];

        // does the group exist
        if ($this->id === 0 || !BackendGroupsModel::exists($this->id)) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }

        parent::execute();

        $this->record = BackendGroupsModel::get($this->id);

        BackendGroupsModel::delete($this->id);

        $this->redirect(BackendModel::createUrlForAction(
            'Index',
            null,
            null,
            ['report' => 'deleted', 'var' => $this->record['name']]
        ));
    }
}
