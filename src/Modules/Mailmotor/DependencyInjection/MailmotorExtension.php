<?php

namespace ForkCMS\Modules\Mailmotor\DependencyInjection;

use ForkCMS\Core\Domain\DependencyInjection\ForkModuleExtension;
use ForkCMS\Modules\Mailmotor\DependencyInjection\Compiler\MailmotorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your module configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MailmotorExtension extends ForkModuleExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MailmotorCompilerPass());
    }
}
