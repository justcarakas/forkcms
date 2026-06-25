<?php

namespace ForkCMS\Core\Domain\Settings;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use ForkCMS\Core\Domain\Doctrine\ForkDBALTypeName;
use JsonException;

use function is_resource;
use function stream_get_contents;

final class SettingsBagDBALType extends JsonType
{
    use ForkDBALTypeName;

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof SettingsBag) {
            throw new ConversionException(sprintf(
                'Could not convert PHP value of type "%s" to type "%s"',
                get_debug_type($value),
                static::class
            ));
        }

        try {
            return $value->asJsonString();
        } catch (JsonException $e) {
            throw new ConversionException('Could not serialize value to JSON: ' . $e->getMessage(), 0, $e);
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?SettingsBag
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        try {
            return SettingsBag::fromJsonString($value);
        } catch (JsonException $e) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to type "%s"',
                $value,
                static::class
            ), 0, $e);
        }
    }
}
