<?php

namespace ForkCMS\Modules\Profiles\Frontend\Actions;

use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Modules\Profiles\Frontend\Helper\Authentication as FrontendProfilesAuthentication;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/**
 * You could use this as some kind of dashboard where you can show an activity
 * stream, some statistics, ...
 */
class Index extends FrontendBaseBlock
{
    public function execute(): void
    {
        if (!FrontendProfilesAuthentication::isLoggedIn()) {
            throw new InsufficientAuthenticationException('You need to log in to see your dashboard');
        }

        parent::execute();
        $this->loadTemplate();
    }
}
