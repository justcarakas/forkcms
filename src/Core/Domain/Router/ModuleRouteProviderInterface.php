<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Router;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag(self::class)]
interface ModuleRouteProviderInterface
{
    public function getRouteCollection(): RouteCollection;
}
