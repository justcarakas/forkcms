<?php

namespace ForkCMS\Core\DependencyInjection;

use ForkCMS\Core\Domain\Router\ModuleRouteProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

final readonly class CoreRouteLoader implements ModuleRouteProviderInterface
{
    public function __construct(
        #[Autowire(service: 'routing.loader.yml')]
        private YamlFileLoader $yamlFileLoader,
    ) {
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->yamlFileLoader->load(__DIR__ . '/../config/routes.yaml');
    }
}
