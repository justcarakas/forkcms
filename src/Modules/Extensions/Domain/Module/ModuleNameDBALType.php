<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

class ModuleNameDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return ModuleName::fromString($value);
    }
}
