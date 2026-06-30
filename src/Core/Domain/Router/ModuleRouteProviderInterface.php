<?php

namespace ForkCMS\Core\Domain\Router;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag(self::class)]
interface ModuleRouteProviderInterface
{
    public function getRouteCollection(): RouteCollection;
}
