<?php

namespace ForkCMS;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private bool $isInstalled;

    public function __construct(string $environment, bool $debug)
    {
        $this->isInstalled = file_exists(__DIR__ . '/../.env.local');

        if (!$this->isInstalled) {
            $environment = str_contains($environment, 'test') ? 'test_install' : 'install';
        }

        parent::__construct($environment, $debug);
    }

    private function isInstalled(): bool
    {
        return $this->isInstalled || str_ends_with($this->environment, 'installer');
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        if ($this->isInstalled()) {
            $this->configureLiveContainer($container);

            return;
        }

        $this->configureInstallerContainer($container);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        if ($this->isInstalled()) {
            $this->configureLiveRoutes($routes);

            return;
        }

        $this->configureInstallerRoutes($routes);
    }

    private function configureLiveContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        $container->import('../config/services.yaml');
        $container->import('../config/{services}_' . $this->environment . '.yaml');
    }

    private function configureInstallerContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/install/*.yaml');
        if ($this->environment === 'test_install') {
            $container->import('../config/{packages}/test_install/*.yaml');
        }

        $container->import('../config/{services}_install/*.yaml');
        if ($this->environment === 'test_install') {
            $container->import('../config/{services}_test_install/*.yaml');
        }
    }

    private function configureLiveRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__) . '/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }

    private function configureInstallerRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/install/*.yaml');
    }
}
