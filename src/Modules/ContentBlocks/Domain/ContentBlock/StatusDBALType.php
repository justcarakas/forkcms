<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

class StatusDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return Status::fromString($value);
    }
}
