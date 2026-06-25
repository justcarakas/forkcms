<?php

namespace ForkCMS\Core\Domain\Router;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag('forkcms.routing.loader')]
interface ModuleRouteProviderInterface
{
    public function getRouteCollection(): RouteCollection;
}
