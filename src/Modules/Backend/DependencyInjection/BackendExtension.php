<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\DependencyInjection;

use ForkCMS\Core\Domain\DependencyInjection\ForkModuleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BackendExtension extends ForkModuleExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('services.yaml');
        if ($container->getParameter('fork.is_installed')) {
            $this->getLoader($container)->load('controllers.yaml');
        }
        if (!$container->getParameter('fork.is_installed')) {
            $this->getLoader($container)->load('services_install.yaml');
        }
    }
}
