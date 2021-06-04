<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Authentication\AuthenticationStepConfiguration;
use ForkCMS\Core\Installer\Domain\Authentication\AuthenticationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticationController extends AbstractStepController
{
    public function __invoke(Request $request): Response
    {
        return $this->handleInstallationStep(
            AuthenticationType::class,
            AuthenticationStepConfiguration::class,
            $request
        );
    }
}
