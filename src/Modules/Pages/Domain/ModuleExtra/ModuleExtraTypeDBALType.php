<?php

namespace ForkCMS\Modules\Pages\Domain\ModuleExtra;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class ModuleExtraTypeDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return new ModuleExtraType($value);
    }
}
