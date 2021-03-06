<?php

namespace ForkCMS\Core\Domain\Doctrine;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

abstract class ValueObjectDBALType extends StringType
{
    final public function convertToPHPValue($value, AbstractPlatform $platform): ?Stringable
    {
        if ($value === null) {
            return null;
        }

        return $this->fromString($value);
    }

    final public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $this->toString($value);
    }

    public function getName(): string
    {
        $matches = [];
        if (
            preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z][\w]*)\\\Domain\\\([A-Z][\w]*)\\\([A-Z][\w]*)DBALType$/',
                static::class,
                $matches
            )
        ) {
            return sprintf(
                'modules__%s__%s__%s',
                Container::underscore($matches[1]),
                Container::underscore($matches[2]),
                Container::underscore($matches[3])
            );
        }

        $matches = [];
        if (
            preg_match(
                '/^ForkCMS\\\Core\\\Domain\\\([A-Z][\w]*)\\\([A-Z][\w]*)DBALType$/',
                static::class,
                $matches
            )
        ) {
            return sprintf(
                'core__%s__%s',
                Container::underscore($matches[1]),
                Container::underscore($matches[2])
            );
        }

        throw new InvalidArgumentException('Cauld not automatically determine the unique DBAL type name');
    }

    abstract protected function fromString(string $value): Stringable;

    protected function toString(Stringable $value): string
    {
        return (string)$value;
    }
}
