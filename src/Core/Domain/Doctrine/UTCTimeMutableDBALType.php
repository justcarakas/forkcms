<?php

namespace ForkCMS\Core\Domain\Doctrine;

use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\DBAL\Types\Types;

class UTCTimeMutableDBALType extends TimeType
{
    use UTCDBALTrait;

    public static function getName(): string
    {
        return Types::TIME_MUTABLE;
    }

    /**
     * @param DateTime|null $time
     * @param AbstractPlatform $platform
     *
     * @return string|null
     */
    #[\Override]
    public function convertToDatabaseValue(mixed $time, AbstractPlatform $platform): ?string
    {
        if ($time instanceof DateTime) {
            $time->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($time, $platform);
    }

    /**
     * @param string|null|DateTime $timeString
     * @param AbstractPlatform $platform
     *
     * @throws ConversionException
     *
     * @return DateTime|null
     */
    #[\Override]
    public function convertToPHPValue(mixed $timeString, AbstractPlatform $platform): ?DateTime
    {
        if (null === $timeString || $timeString instanceof DateTime) {
            return $timeString;
        }

        $time = DateTime::createFromFormat($platform->getTimeFormatString(), $timeString, self::getUtc());

        if (!$time) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to Doctrine Type "%s" using format "%s"',
                $timeString,
                static::class,
                $platform->getTimeFormatString()
            ));
        }

        // set time zone
        $time->setTimezone(self::getDefaultTimeZone());

        return $time;
    }
}
