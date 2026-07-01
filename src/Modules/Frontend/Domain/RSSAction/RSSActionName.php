<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use Stringable;

final class RSSActionName implements Stringable
{
    use NamedIdentifier;
}
