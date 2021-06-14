<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class TypeDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return Type::fromString($value);
    }
}
