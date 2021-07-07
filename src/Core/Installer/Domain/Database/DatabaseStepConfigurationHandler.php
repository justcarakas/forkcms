<?php

namespace ForkCMS\Core\Installer\Domain\Database;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class DatabaseStepConfigurationHandler implements CommandHandlerInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function __invoke(DatabaseStepConfiguration $databaseStepConfiguration): void
    {
        InstallerConfiguration::fromSession($this->session)->withDatabaseStep($databaseStepConfiguration);
    }
}
