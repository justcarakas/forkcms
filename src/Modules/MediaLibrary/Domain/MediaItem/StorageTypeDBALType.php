<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class StorageTypeDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return StorageType::fromString($value);
    }
}
