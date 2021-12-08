<?php

namespace ForkCMS\Core\Domain\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

abstract class ValueObjectDBALType extends StringType
{
    use ForkDBALTypeName;

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

    abstract protected function fromString(string $value): Stringable;

    protected function toString(Stringable $value): string
    {
        return (string) $value;
    }
}
