<?php

namespace ForkCMS\Modules\Settings\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use SpoonFilter;

/**
 * This is the email-action, it will display a form to set email settings
 */
class Email extends BackendBaseActionIndex
{
    /**
     * Is the user a god user?
     *
     * @var bool
     */
    protected $isGod = false;

    /**
     * The form instance
     *
     * @var BackendForm
     */
    private $form;

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
        $this->isGod = BackendAuthentication::getUser()->isGod();

        $this->form = new BackendForm('settingsEmail');

        // email settings
        $mailerFrom = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_from');
        $this->form->addText('mailer_from_name', $mailerFrom['name'] ?? '');
        $this->form
            ->addText('mailer_from_email', $mailerFrom['email'] ?? '')
            ->setAttribute('type', 'email')
        ;
        $mailerTo = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_to');
        $this->form->addText('mailer_to_name', $mailerTo['name'] ?? '');
        $this->form
            ->addText('mailer_to_email', $mailerTo['email'] ?? '')
            ->setAttribute('type', 'email')
        ;
        $mailerReplyTo = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_reply_to');
        $this->form->addText('mailer_reply_to_name', $mailerReplyTo['name'] ?? '');
        $this->form
            ->addText('mailer_reply_to_email', $mailerReplyTo['email'] ?? '')
            ->setAttribute('type', 'email')
        ;

        if ($this->isGod) {
            $mailerType = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_type', 'sendmail');
            $this->form->addDropdown('mailer_type', ['sendmail' => 'sendmail', 'smtp' => 'SMTP'], $mailerType);

            // smtp settings
            $this->form->addText('smtp_server', $this->get(ModuleSettingRepository::class)->get('Core', 'smtp_server', ''));
            $this->form->addText('smtp_port', $this->get(ModuleSettingRepository::class)->get('Core', 'smtp_port', 25));
            $this->form->addText('smtp_username', $this->get(ModuleSettingRepository::class)->get('Core', 'smtp_username', ''));
            $this->form->addPassword('smtp_password', $this->get(ModuleSettingRepository::class)->get('Core', 'smtp_password', ''));
            $this->form->addDropdown(
                'smtp_secure_layer',
                ['no' => SpoonFilter::ucfirst(BL::lbl('None')), 'ssl' => 'SSL', 'tls' => 'TLS'],
                $this->get(ModuleSettingRepository::class)->get('Core', 'smtp_secure_layer', 'no')
            );
        }

        $this->template->assign('isGod', $this->isGod);
    }

    protected function parse(): void
    {
        parent::parse();

        // parse the form
        $this->form->parse($this->template);
    }

    private function validateForm(): void
    {
        // is the form submitted?
        if ($this->form->isSubmitted()) {
            // validate required fields
            $this->form->getField('mailer_from_name')->isFilled(BL::err('FieldIsRequired'));
            $this->form->getField('mailer_from_email')->isEmail(BL::err('EmailIsInvalid'));
            $this->form->getField('mailer_to_name')->isFilled(BL::err('FieldIsRequired'));
            $this->form->getField('mailer_to_email')->isEmail(BL::err('EmailIsInvalid'));
            $this->form->getField('mailer_reply_to_name')->isFilled(BL::err('FieldIsRequired'));
            $this->form->getField('mailer_reply_to_email')->isEmail(BL::err('EmailIsInvalid'));

            if ($this->isGod) {
                // SMTP type was chosen
                if ($this->form->getField('mailer_type')->getValue() == 'smtp') {
                    // server & port are required
                    $this->form->getField('smtp_server')->isFilled(BL::err('FieldIsRequired'));
                    $this->form->getField('smtp_port')->isFilled(BL::err('FieldIsRequired'));
                }
            }

            // no errors ?
            if ($this->form->isCorrect()) {
                // e-mail settings
                $this->get(ModuleSettingRepository::class)->set(
                    'Core',
                    'mailer_from',
                    [
                        'name' => $this->form->getField('mailer_from_name')->getValue(),
                        'email' => $this->form->getField('mailer_from_email')->getValue(),
                    ]
                );
                $this->get(ModuleSettingRepository::class)->set(
                    'Core',
                    'mailer_to',
                    [
                        'name' => $this->form->getField('mailer_to_name')->getValue(),
                        'email' => $this->form->getField('mailer_to_email')->getValue(),
                    ]
                );
                $this->get(ModuleSettingRepository::class)->set(
                    'Core',
                    'mailer_reply_to',
                    [
                        'name' => $this->form->getField('mailer_reply_to_name')->getValue(),
                        'email' => $this->form->getField('mailer_reply_to_email')->getValue(),
                    ]
                );

                if ($this->isGod) {
                    $this->get(ModuleSettingRepository::class)->set(
                        'Core',
                        'mailer_type',
                        $this->form->getField('mailer_type')->getValue()
                    );

                    // smtp settings
                    $this->get(ModuleSettingRepository::class)->set(
                        'Core',
                        'smtp_server',
                        $this->form->getField('smtp_server')->getValue()
                    );
                    $this->get(ModuleSettingRepository::class)->set('Core', 'smtp_port', $this->form->getField('smtp_port')->getValue());
                    $this->get(ModuleSettingRepository::class)->set(
                        'Core',
                        'smtp_username',
                        $this->form->getField('smtp_username')->getValue()
                    );
                    $this->get(ModuleSettingRepository::class)->set(
                        'Core',
                        'smtp_password',
                        $this->form->getField('smtp_password')->getValue()
                    );
                    $this->get(ModuleSettingRepository::class)->set(
                        'Core',
                        'smtp_secure_layer',
                        $this->form->getField('smtp_secure_layer')->getValue()
                    );
                }

                // assign report
                $this->template->assign('report', true);
                $this->template->assign('reportMessage', BL::msg('Saved'));
            }
        }
    }
}
