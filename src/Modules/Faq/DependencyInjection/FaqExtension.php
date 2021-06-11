<?php

namespace ForkCMS\Modules\Faq\DependencyInjection;

use ForkCMS\Core\Domain\DependencyInjection\ForkModuleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your module configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
final class FaqExtension extends ForkModuleExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('doctrine.yaml');
    }
}
