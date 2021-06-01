<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class InstallerConnectionFactory extends ConnectionFactory
{
    private SessionInterface $session;

    public function __construct(array $typesConfig, SessionInterface $session)
    {
        parent::__construct($typesConfig);

        $this->session = $session;
    }

    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ): Connection {
        try {
            $installationData = $this->getInstallationData();
            if ($installationData->getDatabaseHostname() === null) {
                return $this->getInstallerConnection($params, $config, $eventManager);
            }

            $params['host'] = $installationData->getDatabaseHostname();
            $params['port'] = $installationData->getDatabasePort();
            $params['dbname'] = $installationData->getDatabaseName();
            $params['user'] = $installationData->getDatabaseUsername();
            $params['password'] = $installationData->getDatabasePassword();

            //continue with regular connection creation using new params
            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        } catch (ConnectionException | DBALException $e) {
            return $this->getInstallerConnection($params, $config, $eventManager);
        }
    }

    private function getInstallationData(): InstallationData
    {
        return InstallationData::fromSession($this->session);
    }

    private function getInstallerConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null
    ): Connection {
        $normalConnection = DriverManager::getConnection($params, $config, $eventManager);

        return new Connection($params, $normalConnection->getDriver(), $config, $eventManager);
    }
}