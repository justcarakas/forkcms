<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class TypeDBALType extends TextType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'VARCHAR(7)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Type
    {
        if ($value === null) {
            return null;
        }

        return Type::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string) $value;
    }

    public function getName(): string
    {
        return 'modules_internationalisation_type';
    }
}
