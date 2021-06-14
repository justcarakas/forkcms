<?php

namespace ForkCMS\Modules\Backend\Domain\Navigation;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

class ActionSlugDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return ActionSlug::fromSlug($value);
    }
}
