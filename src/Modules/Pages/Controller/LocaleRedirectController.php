<?php

namespace ForkCMS\Modules\Pages\Controller;

use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

final class LocaleRedirectController
{
    public const ROUTE_LOCALE_REDIRECT = 'pages_page_locale_redirect';

    public function __construct(
        private readonly InstalledLocaleRepository $installedLocaleRepository,
        private readonly RouterInterface $router
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->attributes->get('_route') === self::ROUTE_LOCALE_REDIRECT) {
            $locale = $request->getPreferredLanguage($this->installedLocaleRepository->findRedirectLocales());
            try {
                $path = $this->router->generate(
                    self::ROUTE_LOCALE_REDIRECT . '.' . $locale,
                    [
                        'path' => $request->attributes->get('path'),
                    ]
                );
            } catch (RouteNotFoundException) {
                $path = $this->router->generate(
                    self::ROUTE_LOCALE_REDIRECT . '.' . $request->attributes->get('default_locale'),
                    [
                        'path' => $request->attributes->get('path'),
                    ]
                );
            }

            return new RedirectResponse($path, Response::HTTP_TEMPORARY_REDIRECT);
        }

        throw new NotFoundHttpException('Page not found');
    }
}
