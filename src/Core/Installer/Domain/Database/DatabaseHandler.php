<?php

namespace ForkCMS\Core\Installer\Domain\Database;

use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use ForkCMS\Core\Installer\Domain\Installer\InstallerHandler;

/**
 * Validates and saves the data from the databases form
 */
final class DatabaseHandler extends InstallerHandler
{
    public function processInstallationData(InstallationData $installationData): InstallationData
    {
        return $installationData;
    }
}
