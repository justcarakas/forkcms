<?php

namespace ForkCMS\Modules\Installer\Domain\Installer;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Extensions\Domain\Module\Command\InstallModules;
use ForkCMS\Modules\Installer\Domain\Configuration\ConfigurationParser;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class InstallForkCMSHandler implements CommandHandlerInterface
{
    public function __construct(
        private ConfigurationParser $configurationParser,
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(InstallForkCMS $installForkCMS): void
    {
        // extend execution limit
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $installerConfiguration = $installForkCMS->installerConfiguration;
        if ($installerConfiguration->saveConfiguration) {
            $this->configurationParser->toYamlFile($installerConfiguration);
        }

        $this->commandBus->dispatch(new InstallModules(...$installerConfiguration->modules));

        $this->configurationParser->toDotEnvFile($installerConfiguration);
    }
}
