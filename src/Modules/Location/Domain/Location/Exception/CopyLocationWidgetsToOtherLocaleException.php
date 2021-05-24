<?php

namespace ForkCMS\Modules\Location\Domain\Location\Exception;

use ForkCMS\Modules\Location\Domain\Location\Command\CopyLocationWidgetsToOtherLocale;

class CopyLocationWidgetsToOtherLocaleException extends \Exception
{
    public static function forWrongCommand(): self
    {
        return new self('The handler expects a ' . CopyLocationWidgetsToOtherLocale::class . ' class.');
    }
}
