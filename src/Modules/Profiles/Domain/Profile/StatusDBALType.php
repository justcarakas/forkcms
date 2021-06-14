<?php

namespace ForkCMS\Modules\Profiles\Domain\Profile;

use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

class StatusDBALType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return Status::fromString($value);
    }
}
