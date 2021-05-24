<?php

namespace ForkCMS\Modules\Profiles\Frontend\Actions;

use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Modules\Profiles\Frontend\Helper\Authentication as FrontendProfilesAuthentication;

class Logout extends FrontendBaseBlock
{
    public function execute(): void
    {
        if (FrontendProfilesAuthentication::isLoggedIn()) {
            FrontendProfilesAuthentication::logout();
        }

        $this->redirect(SITE_MULTILANGUAGE ? SITE_URL . '/' . LANGUAGE : SITE_URL);
    }
}
