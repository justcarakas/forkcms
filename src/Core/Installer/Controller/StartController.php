<?php

namespace ForkCMS\Core\Installer\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

final class StartController extends AbstractStepController
{
    public function __invoke(): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('install_step1'));
    }
}
