<?php

namespace ForkCMS\Modules\Pages\Controller;

use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

final class LocaleRedirectController
{
    public const ROUTE_MULTILINGUAL = 'pages_page_multilingual';
    public const ROUTE_MONOLINGUAL = 'pages_page_monolingual';

    public function __construct(
        private readonly InstalledLocaleRepository $installedLocaleRepository,
        private readonly RouterInterface $router
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (
            $request->attributes->get('_route') === self::ROUTE_MONOLINGUAL
            || $request->getPathInfo() === '/'
        ) {
            return new RedirectResponse(
                $this->router->generate(
                    self::ROUTE_MULTILINGUAL,
                    [
                        '_locale' => $request->getPreferredLanguage(
                            $this->installedLocaleRepository->findRedirectLocales()
                        ),
                        'path' => $request->attributes->get('path'),
                    ]
                )
            );
        }

        throw new NotFoundHttpException('Page not found');
    }
}
