<?php

namespace Backend\Modules\Locale\Domain\Locale\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

final class TypeDBALType extends TextType
{
    public function getName(): string
    {
        return 'locale_type';
    }

    /**
     * @param string $type
     * @param AbstractPlatform $platform
     *
     * @return Type
     */
    public function convertToPHPValue($type, AbstractPlatform $platform): Type
    {
        return new Type($type);
    }

    /**
     * @param Type $type
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValue($type, AbstractPlatform $platform): string
    {
        return $type->getValue();
    }
}
