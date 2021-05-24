<?php

namespace ForkCMS\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The AppKernel provides a proper way to handle a request and transform it into a response.
 */
class AppKernel extends Kernel
{
    /**
     * Load all the bundles we'll be using in our application.
     */
    public function registerBundles(): array
    {
        // @TODO verify what is left here
        $bundles = [
            new \ForkCMS\Bundle\InstallerBundle\ForkCMSInstallerBundle(),
            new \ForkCMS\Bundle\CoreBundle\ForkCMSCoreBundle(),
            new \Backend\Modules\MediaLibrary\MediaLibrary(),
            // @TODO update mailmotor once symfony is upgraded
            //new \Backend\Modules\Mailmotor\Mailmotor(),
            //new \MailMotor\Bundle\MailMotorBundle\MailMotorMailMotorBundle(),
            //new \MailMotor\Bundle\MailChimpBundle\MailMotorMailChimpBundle(),
            //new \MailMotor\Bundle\CampaignMonitorBundle\MailMotorCampaignMonitorBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $fs = new Filesystem();

        if ($fs->exists(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yaml')) {
            $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yaml');
        }
    }
}
