<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;

final class InstallForkCMS
{
    public function __construct(private InstallerConfiguration $installerConfiguration)
    {
    }

    public function getInstallerConfiguration(): InstallerConfiguration
    {
        return $this->installerConfiguration;
    }
}
