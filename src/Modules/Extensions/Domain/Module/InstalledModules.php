<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\PDO\ForkConnection;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final class InstalledModules
{
    /** @var ModuleName[] needed for the console install command  */
    private static array $modulesToInstall = [];

    public function __construct(private bool $forkIsInstalled)
    {
    }

    public static function fromContainer(ContainerInterface $container): InstalledModules
    {
        return new self($container->getParameter('fork.is_installed'));
    }

    /** @return ModuleName[] */
    public function __invoke(): array
    {
        if (!$this->forkIsInstalled) {
            if (count(self::$modulesToInstall) > 0) {
                return self::$modulesToInstall;
            }

            return InstallerConfiguration::fromSession(new Session())->getModules() ?? [];
        }

        return ForkConnection::get()->getInstalledModules();
    }

    public static function setModulesToInstall(ModuleName ...$modulesToInstall): void
    {
        self::$modulesToInstall = $modulesToInstall;
    }
}
