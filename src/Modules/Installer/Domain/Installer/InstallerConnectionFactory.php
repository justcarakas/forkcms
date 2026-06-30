<?php

namespace ForkCMS\Modules\Installer\Domain\Installer;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Exception;
use ForkCMS\Modules\Installer\Domain\Configuration\InstallerConfiguration;

final readonly class InstallerConnectionFactory
{
    /** @param mixed[][] $typesConfig */
    public function __construct(private array $typesConfig = [])
    {
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $mappingTypes
     */
    public function createConnection(
        array $params,
        ?Configuration $config = null,
        array $mappingTypes = []
    ): Connection {
        try {
            $installationData = InstallerConfiguration::fromCache();
            if (!$installationData->hasStep(InstallerStep::DATABASE)) {
                return $this->getInstallerConnection($config, $mappingTypes);
            }

            $params['host'] = $installationData->databaseHostname;
            $params['port'] = $installationData->databasePort;
            $params['dbname'] = $installationData->databaseName;
            $params['user'] = $installationData->databaseUsername;
            $params['password'] = $installationData->databasePassword;

            return new ConnectionFactory($this->typesConfig)->createConnection($params, $config, $mappingTypes);
        } catch (Exception) {
            return $this->getInstallerConnection($config, $mappingTypes);
        }
    }

    /** @param array<string, string> $mappingTypes */
    private function getInstallerConnection(?Configuration $config = null, array $mappingTypes = []): Connection
    {
        return new ConnectionFactory($this->typesConfig)->createConnection(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            $config,
            $mappingTypes
        );
    }
}
