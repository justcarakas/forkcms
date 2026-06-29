<?php

namespace ForkCMS\Core\Domain\Doctrine;

trait ForkDBALTypeName
{
    public static function getName(): string
    {
        return static::class;
    }
}
