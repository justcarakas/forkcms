<?php

namespace Backend\Modules\Locale\Domain\Locale\Name;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

final class NameDBALType extends TextType
{
    public function getName(): string
    {
        return 'locale_name';
    }

    /**
     * @param string $name
     * @param AbstractPlatform $platform
     *
     * @return Name
     */
    public function convertToPHPValue($name, AbstractPlatform $platform): Name
    {
        return new Name($name);
    }

    /**
     * @param Name $nameDBALName
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValue($nameDBALName, AbstractPlatform $platform): string
    {
        return $nameDBALName->getValue();
    }
}
