<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Theme\Command;

use ForkCMS\Modules\Extensions\Domain\Theme\InstallableTheme;

final readonly class InstallTheme
{
    public function __construct(public InstallableTheme $installableTheme)
    {
    }
}
