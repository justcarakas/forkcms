<?php

namespace ForkCMS\Core\Domain\Router;

use RuntimeException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

abstract class ModuleRouteLoader extends YamlFileLoader
{
    private bool $isLoaded = false;

    final public function __construct(FileLocatorInterface $locator, string $env = null)
    {
        parent::__construct($locator, $env);
    }

    /** @return string[] */
    abstract protected function getRouterFilePaths(): array;

    final public function load($file, string $type = null): RouteCollection
    {
        if (true === $this->isLoaded) {
            throw new RuntimeException('Do not add the ' . static::class . ' loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->getRouterFilePaths() as $routerFilePath) {
            $routes->addCollection(parent::load($routerFilePath, $type));
        }

        $this->isLoaded = true;

        return $routes;
    }

    final public function supports($resource, string $type = null): bool
    {
        return 'fork' === $type;
    }

    final public function setCurrentDir(string $dir)
    {
        parent::setCurrentDir($dir);
    }

    final public function getLocator()
    {
        return parent::getLocator();
    }

    final public function import(
        $resource,
        string $type = null,
        bool $ignoreErrors = false,
        string $sourceResource = null,
        $exclude = null
    ) {
        return parent::import(
            $resource,
            $type,
            $ignoreErrors,
            $sourceResource,
            $exclude
        );
    }

    final protected function glob(
        string $pattern,
        bool $recursive,
        &$resource = null,
        bool $ignoreErrors = false,
        bool $forExclusion = false,
        array $excluded = []
    ) {
        return parent::glob(
            $pattern,
            $recursive,
            $resource,
            $ignoreErrors,
            $forExclusion,
            $excluded
        );
    }

    final public function getResolver(): LoaderResolverInterface
    {
        return parent::getResolver();
    }

    final public function setResolver(LoaderResolverInterface $resolver): void
    {
        parent::setResolver($resolver);
    }

    final public function resolve($resource, string $type = null): static|LoaderInterface
    {
        return parent::resolve($resource, $type);
    }

    final protected function parseRoute(RouteCollection $collection, string $name, array $config, string $path): void
    {
        parent::parseRoute($collection, $name, $config, $path);
    }

    final protected function parseImport(RouteCollection $collection, array $config, string $path, string $file): void
    {
        parent::parseImport($collection, $config, $path, $file);
    }

    final protected function validate($config, string $name, string $path): void
    {
        parent::validate($config, $name, $path);
    }
}
