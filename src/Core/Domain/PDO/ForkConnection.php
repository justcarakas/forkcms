<?php

namespace ForkCMS\Core\Domain\PDO;

use PDO;

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
}
