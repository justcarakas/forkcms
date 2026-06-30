<?php

namespace ForkCMS\Modules\Installer\Domain\Database;

use Assert\AssertionFailedException;
use ForkCMS\Core\Domain\PDO\ForkConnection;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Installer\Domain\Installer\InstallerStep;
use ForkCMS\Modules\Installer\Domain\Installer\InstallerStepConfiguration;
use Symfony\Component\Validator\Constraints as Assert;

final class DatabaseStepConfiguration implements InstallerStepConfiguration
{
    #[Assert\NotBlank]
    public ?string $databaseHostname;

    #[Assert\NotBlank]
    public ?string $databaseUsername;

    public ?string $databasePassword;

    #[Assert\NotBlank]
    public ?string $databaseName;

    #[Assert\GreaterThanOrEqual(value: 1)]
    #[Assert\LessThanOrEqual(value: 65535)]
    #[Assert\NotBlank]
    public int $databasePort;

    public function __construct(
        ?string $databaseHostname = null,
        ?string $databaseUsername = null,
        ?string $databasePassword = null,
        ?string $databaseName = null,
        int $databasePort = 3306
    ) {
        $defaultName = $this->getDefaultName();
        $this->databaseHostname = $databaseHostname ?? $_SERVER['MYSQL_HOST'] ?? '127.0.0.1';
        $this->databaseUsername = $databaseUsername ?? $defaultName;
        $this->databasePassword = $databasePassword ?? $defaultName;
        $this->databaseName = $databaseName ?? $defaultName;
        $this->databasePort = $databasePort;
    }

    private function getDefaultName(): string
    {
        $host = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        if (str_starts_with($host, '127.0.0.1') || str_starts_with($host, 'localhost')) {
            return 'forkcms';
        }

        $chunks = explode('.', $host);
        array_pop($chunks);

        return implode('_', $chunks);
    }

    #[\Override]
    public static function fromArray(array $configuration): static
    {
        return new self(
            $configuration['database-hostname'],
            $configuration['database-username'],
            $configuration['database-password'],
            $configuration['database-name'],
            $configuration['database-port'],
        );
    }

    #[\Override]
    public static function fromInstallerConfiguration(InstallerConfiguration $installerConfiguration): static
    {
        if (!$installerConfiguration->hasStep(self::getStep())) {
            return new self();
        }

        return new self(
            $installerConfiguration->databaseHostname,
            $installerConfiguration->databaseUsername,
            $installerConfiguration->databasePassword,
            $installerConfiguration->databaseName,
            $installerConfiguration->databasePort
        );
    }

    public static function fromEnv(): self
    {
        return new self(
            $_ENV['FORK_DATABASE_HOST'],
            $_ENV['FORK_DATABASE_USER'],
            $_ENV['FORK_DATABASE_PASSWORD'],
            $_ENV['FORK_DATABASE_NAME'],
            $_ENV['FORK_DATABASE_PORT'],
        );
    }

    public function canConnectToDatabase(): bool
    {
        try {
            return ForkConnection::testConnection(
                'mysql',
                Ensure::isNotNull($this->databaseHostname),
                Ensure::isNotNull($this->databasePort),
                Ensure::isNotNull($this->databaseName),
                Ensure::isNotNull($this->databaseUsername),
                $this->databasePassword ?? ''
            );
        } catch (AssertionFailedException $e) {
            return false;
        }
    }

    #[\Override]
    public static function getStep(): InstallerStep
    {
        return InstallerStep::DATABASE;
    }
}
