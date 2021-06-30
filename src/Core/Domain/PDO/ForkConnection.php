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
            throw new RuntimeException('Cannot get installed modules from database');
        }

        return array_map(
            static fn(string $moduleName): ModuleName => ModuleName::fromString($moduleName),
            $modulesQuery->fetchAll(PDO::FETCH_COLUMN, 0)
        );
    }

    /** @return array<string, bool> true for the default website locale */
    public function getEnabledLocales(): array
    {
        return array_map(
            static fn(string $isEnabled): bool => $isEnabled,
            $this->query(
                'SELECT locale, isDefaultForWebsite
                FROM locales
                WHERE isEnabledForWebsite = true OR isEnabledForUser = true'
            )->fetchAll(PDO::FETCH_KEY_PAIR)
        );
    }

    /** @return array<string, bool> true for the default website locale */
    public function getWebsiteLocales(): array
    {
        return array_map(
            static fn(string $isEnabled): bool => $isEnabled,
            $this->query(
                'SELECT locale, isDefaultForWebsite
                FROM locales
                WHERE isEnabledForWebsite = true'
            )->fetchAll(PDO::FETCH_KEY_PAIR)
        );
    }
}
