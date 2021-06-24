<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\PDO\ForkConnection;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use PDO;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final class InstalledModules
{
    public function __construct(private bool $forkIsInstalled)
    {
    }

    public static function fromContainer(ContainerInterface $container)
    {
        return new self($container->getParameter('fork.is_installed'));
    }

    /** @return ModuleName[] */
    public function __invoke(): array
    {
        if (!$this->forkIsInstalled) {
            return InstallerConfiguration::fromSession(new Session())?->getModules() ?? [];
        }

        $modulesQuery = ForkConnection::get()->query('SELECT name from Modules');
        if (!$modulesQuery->execute()) {
            throw new RuntimeException('Cannot get installed modules from database');
        }

        return array_map(
            static fn(string $moduleName): ModuleName => ModuleName::fromString($moduleName),
            $modulesQuery->fetchAll(PDO::FETCH_COLUMN, 0)
        );
    }
}
