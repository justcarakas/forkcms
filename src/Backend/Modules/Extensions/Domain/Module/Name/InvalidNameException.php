<?php

namespace Backend\Modules\Extensions\Domain\Module\Name;

use InvalidArgumentException;

final class InvalidNameException extends InvalidArgumentException
{
    public static function onlyAlphanumeric(): self
    {
        return new self('The name may only contain alphanumeric characters');
    }

    public static function mustBeCapitalised(): self
    {
        return new self('The first character must be a capital letter [A-Z]');
    }
}
