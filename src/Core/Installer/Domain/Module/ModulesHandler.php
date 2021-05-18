<?php

namespace ForkCMS\Core\Installer\Domain\Module;

use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use ForkCMS\Core\Installer\Domain\Installer\InstallerHandler;

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
