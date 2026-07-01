<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\Action;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

class ActionNameDBALType extends ValueObjectDBALType
{
    #[\Override]
    protected function fromString(string $value): Stringable
    {
        return ActionName::fromString($value);
    }
}
