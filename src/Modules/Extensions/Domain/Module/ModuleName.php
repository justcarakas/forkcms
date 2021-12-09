<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

final class ModuleName implements Stringable
{
    public const ROLE_PREFIX = 'ROLE_MODULE__';

    use NamedIdentifier;

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (
            !preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z]\w*)/',
                $fullyQualifiedClassName,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Can ony be created from a module classes');
        }

        return self::fromString($matches[1]);
    }

    public static function fromRole(string $role): self
    {
        return self::tryFromRole($role)
            ?? throw new InvalidArgumentException('Role should start with: ' . self::ROLE_PREFIX);
    }

    public static function tryFromRole(string $role): ?self
    {
        if (!str_starts_with($role, self::ROLE_PREFIX)) {
            return null;
        }

        self::fromString(substr($role, strlen(self::ROLE_PREFIX)));
    }

    public function asRole(): string
    {
        return self::ROLE_PREFIX . strtoupper(Container::underscore($this->getName()));
    }
}
