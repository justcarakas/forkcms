<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\Action;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use Stringable;

final class ActionName implements Stringable
{
    use NamedIdentifier;
}
