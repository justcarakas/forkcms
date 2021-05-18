<?php

namespace ForkCMS\Core\Installer\Domain\Login;

use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use ForkCMS\Core\Installer\Domain\Installer\InstallerHandler;

/**
 * Validates and saves the data from the login form
 */
final class LoginHandler extends InstallerHandler
{
    public function processInstallationData(InstallationData $installationData): InstallationData
    {
        return $installationData;
    }
}
