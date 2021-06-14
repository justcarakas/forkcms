<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use Stringable;

final class ModuleName implements Stringable
{
    use NamedIdentifier;
}
