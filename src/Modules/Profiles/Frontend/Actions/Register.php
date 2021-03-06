<?php

namespace ForkCMS\Modules\Profiles\Frontend\Actions;

use ForkCMS\Core\Common\Mailer\Message;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Core\Frontend\Helper\Form as FrontendForm;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language as FL;
use ForkCMS\Core\Frontend\Helper\Model as FrontendModel;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Profiles\Frontend\Helper\Authentication as FrontendProfilesAuthentication;
use ForkCMS\Modules\Profiles\Frontend\Helper\Model as FrontendProfilesModel;

class Register extends FrontendBaseBlock
{
    /**
     * @var FrontendForm
     */
    private $form;

    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();

        if ($this->url->getParameter('registered') === 'true') {
            $this->template->assign('registerIsSuccess', true);
            $this->template->assign('registerHideForm', true);

            return;
        }

        if (FrontendProfilesAuthentication::isLoggedIn()) {
            $this->redirect(SITE_URL);
        }

        $this->buildForm();
        $this->handleForm();
        $this->form->parse($this->template);
    }

    private function buildForm(): void
    {
        $this->form = new FrontendForm('register', null, null, 'registerForm');

        $this->form->addText('display_name')->makeRequired();
        $this->form->addText('email')->makeRequired()->setAttribute('type', 'email');
        $this->form->addPassword('password')->makeRequired()->setAttribute('data-role', 'fork-new-password');
        $this->form->addCheckbox('show_password')->setAttribute('data-role', 'fork-toggle-visible-password');
    }

    private function validateForm(): bool
    {
        $this->form->getField('password')->isFilled(FL::getError('PasswordIsRequired'));
        $this->form->getField('display_name')->isFilled(FL::getError('FieldIsRequired'));
        $txtEmail = $this->form->getField('email');

        if ($txtEmail->isFilled(FL::getError('EmailIsRequired'))
            && $txtEmail->isEmail(FL::getError('EmailIsInvalid'))
            && FrontendProfilesModel::existsByEmail($txtEmail->getValue())) {
            $txtEmail->setError(FL::getError('EmailExists'));
        }

        return $this->form->isCorrect();
    }

    private function createProfile(string $activationKey): array
    {
        $profile = [
            'email' => $this->form->getField('email')->getValue(),
            'password' => FrontendProfilesModel::encryptPassword($this->form->getField('password')->getValue()),
            'status' => 'inactive',
            'display_name' => $this->form->getField('display_name')->getValue(),
            'registered_on' => FrontendModel::getUTCDate(),
            'last_login' => FrontendModel::getUTCDate(null, 0),
            'url' => FrontendProfilesModel::getUrl($this->form->getField('display_name')->getValue()),
        ];

        $profile['id'] = FrontendProfilesModel::insert($profile);

        FrontendProfilesModel::setSettings(
            $profile['id'],
            [
                'language' => LANGUAGE,
                'activation_key' => $activationKey,
            ]
        );

        return $profile;
    }

    private function handleForm(): void
    {
        if (!$this->form->isSubmitted()) {
            return;
        }

        if (!$this->validateForm()) {
            $this->template->assign('registerHasFormError', true);

            return;
        }

        $activationKey = FrontendProfilesModel::getEncryptedString(
            uniqid(microtime(), true),
            FrontendProfilesModel::getRandomString()
        );
        $profile = $this->createProfile($activationKey);

        $this->sendActivationEmail($profile, $activationKey);

        $this->redirect($this->url->getQueryString() . '?registered=true');
    }

    private function sendActivationEmail(array $profile, string $activationKey): void
    {
        $activationUrl = SITE_URL . FrontendNavigation::getUrlForBlock($this->getModule(), 'Activate');
        $from = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_from');
        $replyTo = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_reply_to');
        $message = Message::newInstance(FL::getMessage('RegisterSubject'))
            ->setFrom([$from['email'] => $from['name']])
            ->setTo([$profile['email'] => $profile['display_name']])
            ->setReplyTo([$replyTo['email'] => $replyTo['name']])
            ->parseHtml(
                'Profiles/Layout/Templates/Mails/Register.html.twig',
                ['activationUrl' => $activationUrl . '/' . $activationKey],
                true
            );
        $this->get('mailer')->send($message);
    }
}
