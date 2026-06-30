<?php

namespace ForkCMS\Core\Domain\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Types;

class UTCDateTimeImmutableDBALType extends DateTimeImmutableType
{
    use UTCDBALTrait;

    public static function getName(): string
    {
        return Types::DATETIME_IMMUTABLE;
    }

    /**
     * @param DateTimeImmutable|null $dateTime
     * @param AbstractPlatform $platform
     *
     * @return string|null
     */
    #[\Override]
    public function convertToDatabaseValue(mixed $dateTime, AbstractPlatform $platform): ?string
    {
        if ($dateTime instanceof DateTimeImmutable) {
            $dateTime = $dateTime->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($dateTime, $platform);
    }

    /**
     * @param string|null|DateTimeImmutable $dateTimeString
     * @param AbstractPlatform $platform
     *
     * @throws ConversionException
     *
     * @return DateTimeImmutable|null
     */
    #[\Override]
    public function convertToPHPValue(mixed $dateTimeString, AbstractPlatform $platform): ?DateTimeImmutable
    {
        if (null === $dateTimeString || $dateTimeString instanceof DateTimeImmutable) {
            return $dateTimeString;
        }

        $dateTime = DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            $dateTimeString,
            self::getUtc()
        );

        if (!$dateTime) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to Doctrine Type "%s" using format "%s"',
                $dateTimeString,
                static::class,
                $platform->getDateTimeFormatString()
            ));
        }

        // set time zone
        return $dateTime->setTimezone(self::getDefaultTimeZone());
    }
}
