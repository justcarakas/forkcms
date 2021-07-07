<?php

namespace ForkCMS\Core\Domain\Router;

use RuntimeException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

final class ModuleRouteLoader implements LoaderInterface
{
    private bool $isLoaded = false;
    private LoaderResolverInterface $resolver;

    /** @param iterable<ModuleRouteProviderInterface> $moduleLoaders */
    public function __construct(private iterable $moduleLoaders)
    {
    }

    public function load($resource, string $type = null): RouteCollection
    {
        if (true === $this->isLoaded) {
            throw new RuntimeException('Do not add the ' . static::class . ' loader twice');
        }
        $routes = new RouteCollection();

        foreach ($this->moduleLoaders as $moduleLoader) {
            $routes->addCollection($moduleLoader->getRouteCollection());
        }

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'fork' === $type;
    }

    public function getResolver(): LoaderResolverInterface
    {
        return $this->resolver;
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }
}
