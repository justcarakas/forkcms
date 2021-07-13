<?php

namespace ForkCMS\Core\Installer\Domain\Database;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;

final class DatabaseStepConfigurationHandler implements CommandHandlerInterface
{
    public function __invoke(DatabaseStepConfiguration $databaseStepConfiguration): void
    {
        InstallerConfiguration::toCache(
            InstallerConfiguration::fromCache()->withDatabaseStep($databaseStepConfiguration)
        );
    }
}
