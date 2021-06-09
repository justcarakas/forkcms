<?php

namespace ForkCMS\Modules\Users\Backend\Widgets;

use ForkCMS\Core\Backend\Domain\Widget\Widget as BackendBaseWidget;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;

/**
 * This widget will show the statistics of the authenticated user.
 */
class Statistics extends BackendBaseWidget
{
    public function execute(): void
    {
        $this->setColumn('left');
        $this->setPosition(1);
        $this->parse();
        $this->display();
    }

    private function parse(): void
    {
        // get the logged in user
        $authenticatedUser = BackendAuthentication::getUser();

        // check if we need to show the password strength and parse the label
        $this->template->assign('showPasswordStrength', ($authenticatedUser->getSetting('password_strength') !== 'strong'));
        $this->template->assign('passwordStrengthLabel', BL::lbl($authenticatedUser->getSetting('password_strength')));
    }
}
