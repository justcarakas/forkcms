<?php

namespace ForkCMS\Modules\Frontend\DependencyInjection;

use ForkCMS\Core\Domain\DependencyInjection\ForkModuleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class FrontendExtension extends ForkModuleExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('services.yaml');
        if ($container->getParameter('fork.is_installed')) {
            $this->getLoader($container)->load('controllers.yaml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('doctrine.yaml');
        $config = Yaml::parseFile(__DIR__ . '/../config/security.yaml');
        $container->prependExtensionConfig('security', $config['security']);
    }
}
