<?php

namespace ForkCMS\Core\Domain\Doctrine;

use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Stringable;

abstract class ValueObjectDBALType extends StringType
{
    use ForkDBALTypeName;

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] ??= 255;

        return parent::getSQLDeclaration($column, $platform);
    }

    final public function convertToPHPValue(mixed $value, AbstractPlatform $platform): null|Stringable|BackedEnum
    {
        if ($value === null) {
            return null;
        }

        return $this->fromString($value);
    }

    final public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $this->toString($value);
    }

    abstract protected function fromString(string $value): null|Stringable|BackedEnum;

    protected function toString(Stringable|BackedEnum $value): string
    {
        if ($value instanceof Stringable) {
            return (string) $value;
        }

        return $value->value;
    }
}
