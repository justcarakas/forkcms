<?php

namespace ForkCMS\Core\Installer\Domain\Authentication;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Installer\InstallerConfiguration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class AuthenticationStepConfigurationHandler implements CommandHandlerInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function __invoke(AuthenticationStepConfiguration $authenticationStepConfiguration)
    {
        InstallerConfiguration::fromSession($this->session)->withAuthenticationStep($authenticationStepConfiguration);
    }
}
