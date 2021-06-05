<?php

namespace ForkCMS\Core\Installer\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

final class StartController
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('install_step1'));
    }
}
