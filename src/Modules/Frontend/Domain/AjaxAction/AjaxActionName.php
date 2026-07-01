<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\AjaxAction;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use Stringable;

final class AjaxActionName implements Stringable
{
    use NamedIdentifier;
}
