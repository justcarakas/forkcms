<?php

namespace ForkCMS\Core\Installer\Domain\Database;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Installer\InstallerConfiguration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class DatabaseStepConfigurationHandler implements CommandHandlerInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function __invoke(DatabaseStepConfiguration $databaseStepConfiguration)
    {
        InstallerConfiguration::fromSession($this->session)->withDatabaseStep($databaseStepConfiguration);
    }
}