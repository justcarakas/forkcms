<?php

namespace ForkCMS\Modules\MediaGalleries\Domain\MediaGallery;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class StatusDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return Status::fromString($value);
    }
}
