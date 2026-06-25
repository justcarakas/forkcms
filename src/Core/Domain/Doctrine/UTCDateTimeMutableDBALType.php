<?php

namespace ForkCMS\Core\Domain\Doctrine;

use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class UTCDateTimeMutableDBALType extends DateTimeType
{
    use UTCDBALTrait;

    /**
     * @param DateTime|null $dateTime
     * @param AbstractPlatform $platform
     *
     * @return string|null
     */
    public function convertToDatabaseValue(mixed $dateTime, AbstractPlatform $platform): ?string
    {
        if ($dateTime instanceof DateTime) {
            $dateTime->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($dateTime, $platform);
    }

    /**
     * @param string|null|DateTime $dateTimeString
     * @param AbstractPlatform $platform
     *
     * @throws ConversionException
     *
     * @return DateTime|null
     */
    public function convertToPHPValue(mixed $dateTimeString, AbstractPlatform $platform): ?DateTime
    {
        if (null === $dateTimeString || $dateTimeString instanceof DateTime) {
            return $dateTimeString;
        }

        $dateTime = DateTime::createFromFormat($platform->getDateTimeFormatString(), $dateTimeString, self::getUtc());

        if (!$dateTime) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to Doctrine Type "%s" using format "%s"',
                $dateTimeString,
                static::class,
                $platform->getDateTimeFormatString()
            ));
        }

        // set time zone
        $dateTime->setTimezone(self::getDefaultTimeZone());

        return $dateTime;
    }
}
