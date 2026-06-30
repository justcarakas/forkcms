<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

class ModuleNameDBALType extends ValueObjectDBALType
{
    #[\Override]
    protected function fromString(string $value): Stringable
    {
        return ModuleName::fromString($value);
    }
}
