<?php

namespace ForkCMS\Modules\Profiles\Frontend\Widgets;

use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Core\Frontend\Helper\Form as FrontendForm;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Profiles\Frontend\Helper\Authentication as FrontendProfilesAuthentication;

/**
 * This is a widget with a login form
 */
class LoginBox extends FrontendBaseWidget
{
    /**
     * @var FrontendForm
     */
    private $form;

    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->buildForm();
        $this->parse();
    }

    private function buildForm(): void
    {
        // don't show the form if someone is logged in
        if (FrontendProfilesAuthentication::isLoggedIn()) {
            return;
        }

        $this->form = new FrontendForm(
            'login',
            FrontendNavigation::getUrlForBlock('Profiles', 'Login') . '?queryString=' . $this->url->getQueryString()
        );
        $this->form->addText('email')->makeRequired()->setAttribute('type', 'email');
        $this->form->addPassword('password')->makeRequired();
        $this->form->addCheckbox('remember', true);

        // parse the form
        $this->form->parse($this->template);
    }

    private function parse(): void
    {
        $this->template->assign('isLoggedIn', FrontendProfilesAuthentication::isLoggedIn());

        if (FrontendProfilesAuthentication::isLoggedIn()) {
            $profile = FrontendProfilesAuthentication::getProfile();
            $this->template->assign('profile', $profile->toArray());
        }
    }
}
