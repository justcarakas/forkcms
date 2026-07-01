<?php

namespace ForkCMS\Modules\Installer\Domain\Installer;

use ForkCMS\Modules\Installer\Domain\Configuration\InstallerConfiguration;

final readonly class InstallForkCMS
{
    public function __construct(public InstallerConfiguration $installerConfiguration)
    {
    }
}
