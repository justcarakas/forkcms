<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Doctrine;

trait ForkDBALTypeName
{
    public static function getName(): string
    {
        return static::class;
    }
}
