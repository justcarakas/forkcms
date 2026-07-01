<?php

namespace ForkCMS\Core\Domain\Enum;

trait TryFromNullable
{
    public static function tryFromNullable(?string $value): ?static
    {
        if ($value === null) {
            return null;
        }

        return static::tryFrom($value);
    }
}
