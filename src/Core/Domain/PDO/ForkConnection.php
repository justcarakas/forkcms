<?php

namespace ForkCMS\Core\Domain\PDO;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use PDO;
use RuntimeException;

/**
 * @internal please use doctrine, this is only for use while the kernel is building and we can't use doctrine yet
 */
final class ForkConnection extends PDO
{
    private static ?self $instance = null;

    public static function get(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self(
                sprintf(
                    '%1$s:host=%2$s;port=%3$s;dbname=%4$s',
                    $_ENV['FORK_DATABASE_DRIVER'],
                    $_ENV['FORK_DATABASE_HOST'],
                    $_ENV['FORK_DATABASE_PORT'],
                    $_ENV['FORK_DATABASE_NAME'],
                ), $_ENV['FORK_DATABASE_USER'], $_ENV['FORK_DATABASE_PASSWORD']
            );
        }

        return self::$instance;
    }

    /** @return ModuleName[] */
    public function getInstalledModules(): array
    {
        $modulesQuery = $this->query('SELECT name from Modules');
        if (!$modulesQuery->execute()) {
            $modulesQuery->closeCursor();
            throw new RuntimeException('Cannot get installed modules from database');
        }

        $installedModules = $modulesQuery->fetchAll(PDO::FETCH_COLUMN, 0);
        $modulesQuery->closeCursor();

        return array_map(
            static fn(string $moduleName): ModuleName => ModuleName::fromString($moduleName),
            $installedModules
        );
    }

    /** @return array<string, bool> true for the default website locale */
    public function getEnabledLocales(): array
    {
        $query = $this->query(
            'SELECT locale, isDefaultForWebsite
                FROM locales
                WHERE isEnabledForWebsite = true OR isEnabledForUser = true'
        );
        $enabledLocales = $query->fetchAll(PDO::FETCH_KEY_PAIR);
        $query->closeCursor();

        return array_map(static fn(string $isEnabled): bool => $isEnabled, $enabledLocales);
    }

    /** @return array<string, bool> true for the default website locale */
    public function getWebsiteLocales(): array
    {
        $query = $this->query(
            'SELECT locale, isDefaultForWebsite
                FROM locales
                WHERE isEnabledForWebsite = true'
        );
        $locales = $query->fetchAll(PDO::FETCH_KEY_PAIR);
        $query->closeCursor();

        return array_map(static fn(string $isEnabled): bool => $isEnabled, $locales);
    }

    /** @return string[] */
    public function getTables(): array
    {
        $query = $this->query('SHOW TABLES');
        $tables = $query->fetchAll(self::FETCH_COLUMN, 0);
        $query->closeCursor();

        return $tables;
    }
}
