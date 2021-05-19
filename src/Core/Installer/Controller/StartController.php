<?php

namespace ForkCMS\Core\Installer\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class StartController extends AbstractStepController
{
    public function __invoke(Request $request): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('install_step1'));
    }
}
