<?php

namespace ForkCMS\Core\Installer\Controller;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class StartController
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function __invoke(): Response
    {
        return new RedirectResponse($this->router->generate('install_step1'));
    }
}
