<?php

namespace ForkCMS\Modules\Search\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionAdd as BackendBaseActionAdd;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Search\Backend\Helper\Model as BackendSearchModel;

/**
 * This is the add-action, it will display a form to create a new synonym
 */
class AddSynonym extends BackendBaseActionAdd
{
    public function execute(): void
    {
        parent::execute();
        $this->loadForm();
        $this->validateForm();
        $this->parse();
        $this->display();
    }

    private function loadForm(): void
    {
        $this->form = new BackendForm('addItem');

        $this->form->addText('term', null, 255);
        $this->form->addText('synonym', null, null, 'form-control synonymBox', 'form-control danger synonymBox');
    }

    private function validateForm(): void
    {
        if (!$this->form->isSubmitted()) {
            return;
        }

        $this->form->cleanupFields();
        $this->form->getField('synonym')->isFilled(BL::err('SynonymIsRequired'));
        $this->form->getField('term')->isFilled(BL::err('TermIsRequired'));
        if (BackendSearchModel::existsSynonymByTerm($this->form->getField('term')->getValue())) {
            $this->form->getField('term')->addError(BL::err('TermExists'));
        }

        if (!$this->form->isCorrect()) {
            return;
        }

        $synonym = [
            'term' => $this->form->getField('term')->getValue(),
            'synonym' => $this->form->getField('synonym')->getValue(),
            'language' => BL::getWorkingLanguage(),
        ];

        $id = BackendSearchModel::insertSynonym($synonym);

        $this->redirect(
            BackendModel::createUrlForAction('Synonyms') . '&report=added-synonym&var=' . rawurlencode(
                $synonym['term']
            ) . '&highlight=row-' . $id
        );
    }
}
