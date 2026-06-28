<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

class ModuleNameDBALType extends ValueObjectDBALType
{
    public const string NAME = 'modules__extensions__module__module_name';

    protected function fromString(string $value): Stringable
    {
        return ModuleName::fromString($value);
    }
}
