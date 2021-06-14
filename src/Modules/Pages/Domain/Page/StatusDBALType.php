<?php

namespace ForkCMS\Modules\Pages\Domain\Page;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class StatusDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return new Status($value);
    }
}
