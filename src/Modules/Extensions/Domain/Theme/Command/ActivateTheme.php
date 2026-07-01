<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Theme\Command;

use ForkCMS\Modules\Extensions\Domain\Theme\Theme;

final readonly class ActivateTheme
{
    public function __construct(public Theme $theme)
    {
    }
}
