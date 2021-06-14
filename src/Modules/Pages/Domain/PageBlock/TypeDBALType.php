<?php

namespace ForkCMS\Modules\Pages\Domain\PageBlock;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class TypeDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return new Type($value);
    }
}
