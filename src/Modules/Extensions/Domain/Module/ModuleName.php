<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

final class ModuleName implements Stringable
{
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

    public function asRole(): string
    {
        return 'ROLE_MODULE__' . strtoupper(Container::underscore($this->getName()));
    }
}
