<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaGroup;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class TypeDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return Type::fromString($value);
    }
}
