<?php

namespace Backend\Modules\Locale\Domain\Translation\Type;

use InvalidArgumentException;

final class InvalidTypeException extends InvalidArgumentException
{
    public static function notSupported(string $type): self
    {
        return new self('The type: "' . $type . '" is not supported');
    }
}
