<?php

namespace ForkCMS\Modules\Pages\Domain\ModuleExtra;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class ModuleExtraTypeDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return new ModuleExtraType($value);
    }
}
