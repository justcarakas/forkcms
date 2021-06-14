<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class StorageTypeDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return StorageType::fromString($value);
    }
}
