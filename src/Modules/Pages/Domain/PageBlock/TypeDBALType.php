<?php

namespace ForkCMS\Modules\Pages\Domain\PageBlock;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class TypeDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return new Type($value);
    }
}
