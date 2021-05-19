<?php

namespace ForkCMS\Core\Installer\Domain\Locale;

use Common\Locale;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use ForkCMS\Core\Installer\Domain\Locale as InstallLocale;

class LocaleDBALType extends TextType
{
    const LOCALE = 'locale';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'VARCHAR(5)';
    }

    /**
     * @param string|null $locale
     * @param AbstractPlatform $platform
     *
     * @return Locale|null
     */
    public function convertToPHPValue($locale, AbstractPlatform $platform): ?Locale
    {
        if ($locale === null) {
            return null;
        }

        return InstallLocale::fromString($locale);
    }

    /**
     * @param Locale $locale
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValue($locale, AbstractPlatform $platform): string
    {
        return (string) $locale;
    }

    public function getName(): string
    {
        return self::LOCALE;
    }
}
