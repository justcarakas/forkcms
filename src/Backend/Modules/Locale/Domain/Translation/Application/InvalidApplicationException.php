<?php

namespace Backend\Modules\Locale\Domain\Translation\Application;

use InvalidArgumentException;

final class InvalidApplicationException extends InvalidArgumentException
{
    public static function notSupported(string $application): self
    {
        return new self('The application: "' . $application . '" is not supported');
    }
}
