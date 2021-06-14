<?php

namespace ForkCMS\Modules\Profiles\Domain\Profile;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

class StatusDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return Status::fromString($value);
    }
}
