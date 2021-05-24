<?php

namespace ForkCMS\Modules\Profiles\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Profiles\Backend\Helper\Model as BackendProfilesModel;

/**
 * This is the edit_group-action, it will display a form to edit an existing group.
 */
class EditGroup extends BackendBaseActionEdit
{
    /**
     * Info about the current group.
     *
     * @var array
     */
    private $group;

    public function execute(): void
    {
        // get parameters
        $this->id = $this->getRequest()->query->getInt('id');

        // does the item exists
        if ($this->id !== 0 && BackendProfilesModel::existsGroup($this->id)) {
            parent::execute();
            $this->getData();
            $this->loadForm();
            $this->validateForm();
            $this->loadDeleteForm();
            $this->parse();
            $this->display();
        } else {
            $this->redirect(BackendModel::createUrlForAction('Groups') . '&error=non-existing');
        }
    }

    private function getData(): void
    {
        // get general info
        $this->group = BackendProfilesModel::getGroup($this->id);
    }

    private function loadForm(): void
    {
        $this->form = new BackendForm('editGroup');
        $this->form->addText('name', $this->group['name'], null, 'form-control title', 'form-control danger title')->makeRequired();
    }

    protected function parse(): void
    {
        parent::parse();

        // assign the active record and additional variables
        $this->template->assign('group', $this->group);

        $this->header->appendDetailToBreadcrumbs($this->group['name']);
    }

    private function validateForm(): void
    {
        // is the form submitted?
        if ($this->form->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->form->cleanupFields();

            // get fields
            $txtName = $this->form->getField('name');

            // name filled in?
            if ($txtName->isFilled(BL::getError('NameIsRequired'))) {
                // name already exists?
                if (BackendProfilesModel::existsGroupName($txtName->getValue(), $this->id)) {
                    // set error
                    $txtName->addError(BL::getError('GroupNameExists'));
                }
            }

            // no errors?
            if ($this->form->isCorrect()) {
                // build item
                $values = ['name' => $txtName->getValue()];

                // update values
                BackendProfilesModel::updateGroup($this->id, $values);

                // everything is saved, so redirect to the overview
                $this->redirect(
                    BackendModel::createUrlForAction('Groups') . '&report=group-saved&var=' . rawurlencode(
                        $values['name']
                    ) . '&highlight=row-' . $this->id
                );
            }
        }
    }

    private function loadDeleteForm(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $this->group['id']],
            ['module' => $this->getModule(), 'action' => 'DeleteGroup']
        );
        $this->template->assign('deleteForm', $deleteForm->createView());
    }
}
