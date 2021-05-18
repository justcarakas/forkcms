<?php

namespace ForkCMS\Core\Installer\Controller;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Twig\Environment;

abstract class AbstractStepController
{
    protected Environment $twig;
    protected Router $router;

    public function __construct(Environment $twig, Router $router)
    {
        $this->twig = $twig;
        $this->router = $router;
    }

    abstract public function __invoke(): ResponseInterface;
}
