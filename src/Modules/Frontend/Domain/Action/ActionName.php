<?php

namespace ForkCMS\Modules\Frontend\Domain\Action;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use Stringable;

final class ActionName implements Stringable
{
    use NamedIdentifier;
}
