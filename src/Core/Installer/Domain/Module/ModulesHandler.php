<?php

namespace ForkCMS\Core\Installer\Domain\Module;

use ForkCMS\Bundle\InstallerBundle\Entity\InstallationData;

/**
 * Validates and saves the data from the modules form
 */
final class ModulesHandler extends InstallerHandler
{
    public function processInstallationData(InstallationData $installationData): InstallationData
    {
        if ($installationData->hasExampleData() === true) {
            $installationData->addModule('Blog');
        }

        return $installationData;
    }
}
