<?php

namespace ForkCMS\Modules\Backend\Domain\Router;

use ForkCMS\Core\Domain\Router\ModuleRouteLoader;

final class BackendRouteLoader extends ModuleRouteLoader
{
    protected function getRouterFilePaths(): array
    {
        return [
            __DIR__ . '/../../config/routes.yaml'
        ];
    }
}
