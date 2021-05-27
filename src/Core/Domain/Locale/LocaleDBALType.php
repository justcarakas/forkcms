<?php

namespace ForkCMS\Core\Domain\Locale;

use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class LocaleDBALType extends TextType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'VARCHAR(5)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Locale
    {
        if ($value === null) {
            return null;
        }

        return Locale::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string) $value;
    }

    public function getName(): string
    {
        return 'core_locale_locale';
    }
}
