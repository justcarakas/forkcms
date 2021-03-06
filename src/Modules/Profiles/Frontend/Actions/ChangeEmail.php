<?php

namespace ForkCMS\Modules\Profiles\Frontend\Actions;

use ForkCMS\Modules\Profiles\Domain\Profile\Profile;
use ForkCMS\Modules\Profiles\Domain\Profile\Status;
use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Core\Frontend\Helper\Form as FrontendForm;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language as FL;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Profiles\Frontend\Helper\Authentication as FrontendProfilesAuthentication;
use ForkCMS\Modules\Profiles\Frontend\Helper\Model as FrontendProfilesModel;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/**
 * Change the e-mail of the current logged in profile.
 */
class ChangeEmail extends FrontendBaseBlock
{
    /**
     * @var FrontendForm
     */
    private $form;

    /**
     * The current profile.
     *
     * @var Profile
     */
    private $profile;

    public function execute(): void
    {
        if (!FrontendProfilesAuthentication::isLoggedIn()) {
            throw new InsufficientAuthenticationException('You need to log in to change your email');
        }

        parent::execute();
        $this->getData();
        $this->loadTemplate();
        $this->buildForm();
        $this->handleForm();
        $this->parse();
    }

    private function getData(): void
    {
        $this->profile = FrontendProfilesAuthentication::getProfile();
    }

    private function buildForm(): void
    {
        $this->form = new FrontendForm('updateEmail', null, null, 'updateEmailForm');
        $this->form->addPassword('password')->makeRequired();
        $this->form->addText('email', $this->profile->getEmail())->makeRequired()->setAttribute('type', 'email');
    }

    private function parse(): void
    {
        // show the success message when the email was changed
        $this->template->assign('updateEmailSuccess', $this->url->getParameter('changedEmail') === 'true');
        $this->form->parse($this->template);
    }

    private function isValidLoginCredentials(string $email, string $password): bool
    {
        $loginStatus = FrontendProfilesAuthentication::getLoginStatus($email, $password);

        return $loginStatus === (string) Status::active();
    }

    private function validateForm(): bool
    {
        $txtPassword = $this->form->getField('password');
        $txtEmail = $this->form->getField('email');

        if ($txtPassword->isFilled(FL::getError('PasswordIsRequired'))
            && !$this->isValidLoginCredentials($this->profile->getEmail(), $txtPassword->getValue())) {
            $txtPassword->addError(FL::getError('InvalidPassword'));
        }

        if ($txtEmail->isFilled(FL::getError('EmailIsRequired'))
            && $txtEmail->isEmail(FL::getError('EmailIsInvalid'))
            && FrontendProfilesModel::existsByEmail($txtEmail->getValue(), $this->profile->getId())) {
            $txtEmail->setError(FL::getError('EmailExists'));
        }

        return $this->form->isCorrect(true);
    }

    private function handleForm(): void
    {
        if (!$this->form->isSubmitted()) {
            return;
        }

        if (!$this->validateForm()) {
            $this->template->assign('updateEmailHasFormError', true);

            return;
        }

        FrontendProfilesModel::update($this->profile->getId(), ['email' => $this->form->getField('email')->getValue()]);

        $this->redirect(
            FrontendNavigation::getUrlForBlock('Profiles', 'ChangeEmail') . '?changedEmail=true'
        );
    }
}
