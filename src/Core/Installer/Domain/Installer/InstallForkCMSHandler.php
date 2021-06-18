<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Configuration\ConfigurationParser;
use ForkCMS\Modules\Extensions\Domain\Module\InstallModules;
use Symfony\Component\Messenger\MessageBusInterface;

final class InstallForkCMSHandler implements CommandHandlerInterface
{
    public function __construct(
        private ConfigurationParser $configurationParser,
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(InstallForkCMS $installForkCMS)
    {
        // extend execution limit
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $installerConfiguration = $installForkCMS->getInstallerConfiguration();
        if ($installerConfiguration->shouldSaveConfiguration()) {
            $this->configurationParser->toYamlFile($installForkCMS->getInstallerConfiguration());
        }

        $this->commandBus->dispatch(new InstallModules(...$installerConfiguration->getModules()));

        $this->configurationParser->toDotEnvFile($installerConfiguration);
    }
}
