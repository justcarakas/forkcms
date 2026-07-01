<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\Widget;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use Stringable;

final class WidgetName implements Stringable
{
    use NamedIdentifier;
}
