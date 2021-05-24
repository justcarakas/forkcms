<?php

namespace ForkCMS\Core\Common\ForkCMS\Bundle\CoreBundle;

use ForkCMS\Core\Common\ForkCMS\Bundle\CoreBundle\DependencyInjection\Compiler\CopyModulesToOtherLocalePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Core bundle for Fork CMS
 */
class ForkCMSCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CopyModulesToOtherLocalePass());
    }
}
