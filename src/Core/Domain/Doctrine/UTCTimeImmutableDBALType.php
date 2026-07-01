<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\Types;

class UTCTimeImmutableDBALType extends TimeImmutableType
{
    use UTCDBALTrait;

    public static function getName(): string
    {
        return Types::TIME_IMMUTABLE;
    }

    /**
     * @param DateTimeImmutable|null $time
     * @param AbstractPlatform $platform
     *
     * @return string|null
     */
    #[\Override]
    public function convertToDatabaseValue(mixed $time, AbstractPlatform $platform): ?string
    {
        if ($time instanceof DateTimeImmutable) {
            $time = $time->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($time, $platform);
    }

    /**
     * @param string|null|DateTimeImmutable $timeString
     * @param AbstractPlatform $platform
     *
     * @throws ConversionException
     *
     * @return DateTimeImmutable|null
     */
    #[\Override]
    public function convertToPHPValue(mixed $timeString, AbstractPlatform $platform): ?DateTimeImmutable
    {
        if (null === $timeString || $timeString instanceof DateTimeImmutable) {
            return $timeString;
        }

        $time = DateTimeImmutable::createFromFormat($platform->getTimeFormatString(), $timeString, self::getUtc());

        if (!$time) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to Doctrine Type "%s" using format "%s"',
                $timeString,
                static::class,
                $platform->getTimeFormatString()
            ));
        }

        // set time zone
        return $time->setTimezone(self::getDefaultTimeZone());
    }
}
