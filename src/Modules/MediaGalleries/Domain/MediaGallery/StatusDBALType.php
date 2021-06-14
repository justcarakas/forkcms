<?php

namespace ForkCMS\Modules\MediaGalleries\Domain\MediaGallery;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class StatusDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return Status::fromString($value);
    }
}
