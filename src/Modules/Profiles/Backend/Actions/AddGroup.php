<?php

namespace ForkCMS\Modules\Profiles\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionAdd as BackendBaseActionAdd;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Profiles\Backend\Helper\Model as BackendProfilesModel;

/**
 * This is the add_group-action, it will display a form to add a group for profiles.
 */
class AddGroup extends BackendBaseActionAdd
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
        $this->form = new BackendForm('addGroup');
        $this->form->addText('name', null, null, 'form-control title', 'form-control danger title')->makeRequired();
    }

    private function validateForm(): void
    {
        // is the form submitted?
        if ($this->form->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->form->cleanupFields();

            // get field
            /** @var $txtName \SpoonFormText */
            $txtName = $this->form->getField('name');

            // name filled in?
            if ($txtName->isFilled(BL::getError('NameIsRequired'))) {
                // name exists?
                if (BackendProfilesModel::existsGroupName($txtName->getValue())) {
                    // set error
                    $txtName->addError(BL::getError('GroupNameExists'));
                }
            }

            // no errors?
            if ($this->form->isCorrect()) {
                // build item
                $values = ['name' => $txtName->getValue()];

                // insert values
                $id = BackendProfilesModel::insertGroup($values);

                // everything is saved, so redirect to the overview
                $this->redirect(
                    BackendModel::createUrlForAction('Groups') . '&report=group-added&var=' . rawurlencode(
                        $values['name']
                    ) . '&highlight=row-' . $id
                );
            }
        }
    }
}
