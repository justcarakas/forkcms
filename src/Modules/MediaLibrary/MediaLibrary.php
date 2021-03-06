<?php

namespace ForkCMS\Modules\MediaLibrary;

use ForkCMS\Modules\MediaLibrary\DependencyInjection\Compiler\StorageProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * MediaLibrary
 */
class MediaLibrary extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StorageProviderPass());
    }
}
