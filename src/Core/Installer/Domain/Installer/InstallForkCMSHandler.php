<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Configuration\ConfigurationParser;

final class InstallForkCMSHandler implements CommandHandlerInterface
{
    public function __construct(private ConfigurationParser $configurationParser)
    {
    }

    public function __invoke(InstallForkCMS $installForkCMS)
    {
        $installerConfiguration = $installForkCMS->getInstallerConfiguration();
        if ($installerConfiguration->shouldSaveConfiguration()) {
            $this->configurationParser->toFile($installForkCMS->getInstallerConfiguration());
        }
    }
}
