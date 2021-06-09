<?php

namespace ForkCMS\Modules\Extensions\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ExtensionsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('doctrine.yaml');
    }

    private function getLoader(ContainerBuilder $container): YamlFileLoader
    {
        return  new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
    }
}
