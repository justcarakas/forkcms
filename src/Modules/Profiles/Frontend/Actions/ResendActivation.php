<?php

namespace ForkCMS\Modules\Profiles\Frontend\Actions;

use ForkCMS\Modules\Profiles\Domain\Profile\Profile;
use ForkCMS\Core\Common\Mailer\Message;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Core\Frontend\Helper\Form as FrontendForm;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language as FL;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Profiles\Frontend\Helper\Authentication as FrontendProfilesAuthentication;
use ForkCMS\Modules\Profiles\Frontend\Helper\Model as FrontendProfilesModel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This is the resend activation-action. It will resend your activation email.
 */
class ResendActivation extends FrontendBaseBlock
{
    /**
     * @var FrontendForm
     */
    private $form;

    /** @var Profile */
    private $profile;

    public function execute(): void
    {
        if (FrontendProfilesAuthentication::isLoggedIn()) {
            throw new NotFoundHttpException();
        }

        parent::execute();
        $this->loadTemplate();
        $this->buildForm();
        $this->handleForm();
        $this->parse();
    }

    private function buildForm(): void
    {
        $this->form = new FrontendForm('resendActivation', null, null, 'resendActivation');
        $this->form->addText('email')->makeRequired()->setAttribute('type', 'email');
    }

    private function parse(): void
    {
        if ($this->url->getParameter('activationHasBeenResent') === 'true') {
            $this->template->assign('resendActivationSuccess', true);
            $this->template->assign('resendActivationHideForm', true);
        }

        $this->form->parse($this->template);
    }

    private function validateForm(): bool
    {
        $txtEmail = $this->form->getField('email');

        if (!$txtEmail->isFilled(FL::getError('EmailIsRequired'))
            || !$txtEmail->isEmail(FL::getError('EmailIsInvalid'))) {
            return $this->form->isCorrect();
        }

        if (!FrontendProfilesModel::existsByEmail($txtEmail->getValue())) {
            $txtEmail->addError(FL::getError('EmailIsInvalid'));

            return $this->form->isCorrect();
        }

        $this->profile = FrontendProfilesModel::get(FrontendProfilesModel::getIdByEmail($txtEmail->getValue()));

        if (!$this->profile->getStatus()->isInactive()) {
            $txtEmail->addError(FL::getError('ProfileIsActive'));
        }

        return $this->form->isCorrect();
    }

    private function handleForm(): void
    {
        if (!$this->form->isSubmitted()) {
            return;
        }

        if (!$this->validateForm()) {
            $this->template->assign('resendActivationHasError', true);

            return;
        }

        $this->resendActivationEmail();

        $this->redirect($this->url->getQueryString() . '?activationHasBeenResent=true');
    }

    private function resendActivationEmail(): void
    {
        $activationUrl = SITE_URL . FrontendNavigation::getUrlForBlock($this->getModule(), 'Activate')
                         . '/' . $this->profile->getSetting('activation_key');
        $from = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_from');
        $replyTo = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_reply_to');
        $message = Message::newInstance(FL::getMessage('RegisterSubject'))
            ->setFrom([$from['email'] => $from['name']])
            ->setTo([$this->profile->getEmail() => $this->profile->getDisplayName()])
            ->setReplyTo([$replyTo['email'] => $replyTo['name']])
            ->parseHtml(
                'Profiles/Layout/Templates/Mails/Register.html.twig',
                ['activationUrl' => $activationUrl],
                true
            );
        $this->get('mailer')->send($message);
    }
}
