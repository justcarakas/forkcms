<?php

namespace ForkCMS\Modules\Search\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Search\Backend\Helper\Model as BackendSearchModel;

/**
 * This action will delete a synonym
 */
class DeleteSynonym extends BackendBaseActionDelete
{
    public function execute(): void
    {
        parent::execute();

        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteSynonym']
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction(
                'Synonyms',
                null,
                null,
                ['error' => 'something-went-wrong']
            ));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $id = (int) $deleteFormData['id'];

        if ($id === 0 || !BackendSearchModel::existsSynonymById($id)) {
            $this->redirect(BackendModel::createUrlForAction('Synonyms', null, null, ['error' => 'non-existing']));

            return;
        }

        $synonym = (array) BackendSearchModel::getSynonym($id);
        BackendSearchModel::deleteSynonym($id);

        $this->redirect(BackendModel::createUrlForAction(
            'Synonyms',
            null,
            null,
            ['report' => 'deleted-synonym', 'var' => $synonym['term']]
        ));
    }
}
