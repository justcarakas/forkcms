<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\Widget;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag(self::class)]
interface WidgetControllerInterface
{
    public function __invoke(Request $request): string;
}
