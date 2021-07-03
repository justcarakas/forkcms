<?php

namespace ForkCMS\Modules\Internationalisation\DependencyInjection;

use ForkCMS\Core\Domain\Router\ModuleRouteProviderInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

final class InternationalisationRouteLoader implements ModuleRouteProviderInterface
{
    public function __construct(private YamlFileLoader $yamlFileLoader)
    {
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->yamlFileLoader->load(__DIR__ . '/../config/routes.yaml');
    }
}
