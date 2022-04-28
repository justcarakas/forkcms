<?php

namespace ForkCMS\Modules\Pages\DependencyInjection;

use ForkCMS\Core\Domain\Router\ModuleRouteProviderInterface;
use ForkCMS\Modules\Pages\Controller\LocaleRedirectController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class PagesRouteLoader implements ModuleRouteProviderInterface
{
    public function __construct(
    ) {
    }

    public function getRouteCollection(): RouteCollection
    {
        $pagesRoutes = new RouteCollection();

        if ($_ENV['SITE_MULTILINGUAL'] === 'true') {
            $pagesRoutes->addPrefix('{_locale}/');
            $pagesRoutes->add(
                LocaleRedirectController::ROUTE_MULTILINGUAL,
                new Route(
                    '{_locale}/{path}',
                    [
                        '_controller' => LocaleRedirectController::class,
                        'path' => '~',
                    ],
                    [
                        'path' => '.+',
                    ],
                ),
                -1
            );
            $pagesRoutes->add(
                LocaleRedirectController::ROUTE_MONOLINGUAL,
                new Route(
                    '{path}',
                    [
                        '_controller' => LocaleRedirectController::class,
                        'path' => '~',
                    ],
                    [
                        'path' => '.+',
                    ],
                ),
                -1
            );
        }

        return $pagesRoutes;
    }
}
