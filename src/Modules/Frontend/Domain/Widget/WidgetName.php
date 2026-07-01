<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\Widget;

use ForkCMS\Core\Domain\Identifier\NamedIdentifier;
use ForkCMS\Modules\Frontend\Domain\Block\BlockName;
use ForkCMS\Modules\Frontend\Domain\Block\Type;
use Stringable;

final class WidgetName implements Stringable, BlockName
{
    use NamedIdentifier;

    #[\Override]
    public function getType(): Type
    {
        return Type::WIDGET;
    }
}
