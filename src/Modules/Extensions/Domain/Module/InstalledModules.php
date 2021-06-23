<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use PDO;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\Session;

final class InstalledModules
{
    public function __construct(private bool $forkIsInstalled = false)
    {
    }

    /** @return ModuleName[] */
    public function __invoke(): array
    {
        if (!$this->forkIsInstalled) {
            return InstallerConfiguration::fromSession(new Session())?->getModules() ?? [];
        }

        $connection = new PDO(
            sprintf(
                '%1$s:host=%2$s;port=%3$s;dbname=%4$s',
                $_ENV['FORK_DATABASE_DRIVER'],
                $_ENV['FORK_DATABASE_HOST'],
                $_ENV['FORK_DATABASE_PORT'],
                $_ENV['FORK_DATABASE_NAME'],
            ), $_ENV['FORK_DATABASE_USER'], $_ENV['FORK_DATABASE_PASSWORD']
        );

        $modulesQuery = $connection->query('SELECT name from Modules');
        if (!$modulesQuery->execute()) {
            throw new RuntimeException('Cannot get installed modules from database');
        }

        return array_map(
            static fn(string $moduleName): ModuleName => ModuleName::fromString($moduleName),
            $modulesQuery->fetchAll(PDO::FETCH_COLUMN, 0)
        );
    }
}
