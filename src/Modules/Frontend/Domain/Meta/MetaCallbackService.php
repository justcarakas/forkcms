<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\Meta;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface MetaCallbackService
{
}
