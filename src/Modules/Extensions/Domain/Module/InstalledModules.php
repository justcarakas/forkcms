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

        return ForkConnection::get()->getInstalledModules();
    }
}
