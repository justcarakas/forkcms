<?php

namespace ForkCMS\Modules\Faq\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;

/**
 * This is the settings-action, it will display a form to set general faq settings
 */
class Settings extends BackendBaseActionEdit
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
        // init settings form
        $this->form = new BackendForm('settings');
        $this->form->addDropdown(
            'overview_number_of_items_per_category',
            array_combine(range(1, 30), range(1, 30)),
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'overview_num_items_per_category', 10)
        );
        $this->form->addDropdown(
            'most_read_number_of_items',
            array_combine(range(1, 10), range(1, 10)),
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'most_read_num_items', 10)
        );
        $this->form->addDropdown(
            'related_number_of_items',
            array_combine(range(1, 10), range(1, 10)),
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'related_num_items', 3)
        );
        $this->form->addCheckbox(
            'allow_multiple_categories',
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'allow_multiple_categories', false)
        );
        $this->form->addCheckbox(
            'spamfilter',
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'spamfilter', false)
        );
        $this->form->addCheckbox(
            'allow_feedback',
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'allow_feedback', false)
        );
        $this->form->addCheckbox(
            'allow_own_question',
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'allow_own_question', false)
        );
        $this->form->addCheckbox(
            'send_email_on_new_feedback',
            $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'send_email_on_new_feedback', false)
        );

        // no Akismet-key, so we can't enable spam-filter
        if ($this->get(ModuleSettingRepository::class)->get('Core', 'akismet_key') == '') {
            $this->form->getField('spamfilter')->setAttribute('disabled', 'disabled');
            $this->template->assign('noAkismetKey', true);
        }
    }

    private function validateForm(): void
    {
        if ($this->form->isSubmitted()) {
            if ($this->form->isCorrect()) {
                // set our settings
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'overview_num_items_per_category',
                    (int) $this->form->getField('overview_number_of_items_per_category')->getValue()
                );
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'most_read_num_items',
                    (int) $this->form->getField('most_read_number_of_items')->getValue()
                );
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'related_num_items',
                    (int) $this->form->getField('related_number_of_items')->getValue()
                );
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'allow_multiple_categories',
                    (bool) $this->form->getField('allow_multiple_categories')->getValue()
                );
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'spamfilter',
                    (bool) $this->form->getField('spamfilter')->getValue()
                );
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'allow_feedback',
                    (bool) $this->form->getField('allow_feedback')->getValue()
                );
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'allow_own_question',
                    (bool) $this->form->getField('allow_own_question')->getValue()
                );
                $this->get(ModuleSettingRepository::class)->set(
                    $this->url->getModule(),
                    'send_email_on_new_feedback',
                    (bool) $this->form->getField('send_email_on_new_feedback')->getValue()
                );
                if ($this->get(ModuleSettingRepository::class)->get('Core', 'akismet_key') === null) {
                    $this->get(ModuleSettingRepository::class)->set($this->url->getModule(), 'spamfilter', false);
                }

                // redirect to the settings page
                $this->redirect(BackendModel::createUrlForAction('Settings') . '&report=saved');
            }
        }
    }
}
