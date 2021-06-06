<?php

namespace ForkCMS\Core\Domain\Application;

use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class ApplicationDBALType extends TextType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'VARCHAR(10)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Application
    {
        if ($value === null) {
            return null;
        }

        return Application::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string) $value;
    }

    public function getName(): string
    {
        return 'core_application_application';
    }
}
