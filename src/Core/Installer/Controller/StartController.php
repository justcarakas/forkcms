<?php

namespace ForkCMS\Core\Installer\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class StartController extends AbstractStepController
{
    public function __invoke(): ResponseInterface
    {
        return new RedirectResponse($this->router->generate('install_step1'));
    }
}
