<?php

namespace ForkCMS\Modules\Profiles\Frontend\Actions;

use ForkCMS\Core\Common\Mailer\Message;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Core\Frontend\Helper\Form as FrontendForm;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language as FL;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Profiles\Frontend\Helper\Authentication as FrontendProfilesAuthentication;
use ForkCMS\Modules\Profiles\Frontend\Helper\Model as FrontendProfilesModel;

/**
 * Request a reset password email.
 */
class ForgotPassword extends FrontendBaseBlock
{
    /**
     * @var FrontendForm
     */
    private $form;

    public function execute(): void
    {
        if (FrontendProfilesAuthentication::isLoggedIn()) {
            $this->redirect(FrontendNavigation::getUrlForBlock('Profiles', 'Settings'));
        }

        parent::execute();
        $this->loadTemplate();
        $this->buildForm();
        $this->handleForm();
        $this->parse();
    }

    private function buildForm(): void
    {
        $this->form = new FrontendForm('forgotPassword', null, null, 'forgotPasswordForm');
        $this->form->addText('email')->makeRequired()->setAttribute('type', 'email');
    }

    private function parse(): void
    {
        $isNewPasswordRequested = $this->url->getParameter('newPasswordRequested') === 'true';
        // show success message when a new password is requested
        $this->template->assign('forgotPasswordSuccess', $isNewPasswordRequested);
        $this->template->assign('forgotPasswordHideForm', $isNewPasswordRequested);
        $this->form->parse($this->template);
    }

    private function validateForm(): bool
    {
        $txtEmail = $this->form->getField('email');

        if ($txtEmail->isFilled(FL::getError('EmailIsRequired'))
            && $txtEmail->isEmail(FL::getError('EmailIsInvalid'))
            && !FrontendProfilesModel::existsByEmail($txtEmail->getValue())) {
            $txtEmail->addError(FL::getError('EmailIsUnknown'));
        }

        return $this->form->isCorrect();
    }

    private function createResetUrl(int $profileId): string
    {
        $key = FrontendProfilesModel::getEncryptedString(
            $profileId . microtime(),
            FrontendProfilesModel::getRandomString()
        );

        FrontendProfilesModel::setSetting($profileId, 'forgot_password_key', $key);

        return SITE_URL . FrontendNavigation::getUrlForBlock($this->getModule(), 'ResetPassword') . '/' . $key;
    }

    private function handleForm(): void
    {
        if (!$this->form->isSubmitted()) {
            return;
        }

        if (!$this->validateForm()) {
            $this->template->assign('forgotPasswordHasError', true);

            return;
        }

        $profileId = FrontendProfilesModel::getIdByEmail($this->form->getField('email')->getValue());
        $this->sendForgotPasswordEmail($profileId, $this->createResetUrl($profileId));

        $this->redirect($this->url->getQueryString() . '?newPasswordRequested=true');
    }

    private function sendForgotPasswordEmail(int $profileId, string $resetUrl): void
    {
        $from = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_from');
        $replyTo = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_reply_to');
        $message = Message::newInstance(FL::getMessage('ForgotPasswordSubject'))
            ->setFrom([$from['email'] => $from['name']])
            ->setTo([$this->form->getField('email')->getValue() => ''])
            ->setReplyTo([$replyTo['email'] => $replyTo['name']])
            ->parseHtml(
                '/Profiles/Layout/Templates/Mails/ForgotPassword.html.twig',
                [
                    'resetUrl' => $resetUrl,
                    'firstName' => FrontendProfilesModel::getSetting($profileId, 'first_name'),
                    'lastName' => FrontendProfilesModel::getSetting($profileId, 'last_name'),
                ],
                true
            );
        $this->get('mailer')->send($message);
    }
}
