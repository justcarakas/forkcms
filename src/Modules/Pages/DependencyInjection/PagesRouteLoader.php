<?php

namespace ForkCMS\Modules\Pages\DependencyInjection;

use ForkCMS\Core\Domain\Router\ModuleRouteProviderInterface;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Pages\Controller\PageController;
use ForkCMS\Modules\Pages\Controller\LocaleRedirectController;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use ForkCMS\Modules\Pages\Domain\Revision\RevisionRepository;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class PagesRouteLoader implements ModuleRouteProviderInterface
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly InstalledLocaleRepository $installedLocaleRepository
    ) {
    }

    public function getRouteCollection(): RouteCollection
    {
        $pagesRoutes = new RouteCollection();
        /** @var Revision[] $revisions */
        $revisions = $this->revisionRepository->createQueryBuilder('r')
            ->leftJoin('r.meta', 'rm')
            ->addSelect('rm')
            ->innerJoin('r.page', 'p')
            ->addSelect('p')
            ->innerJoin('p.revisions', 'pr')
            ->addSelect('pr')
            ->leftJoin('pr.meta', 'prm')
            ->addSelect('prm')
            ->leftJoin('r.parentPage', 'pp')
            ->addSelect('pp')
            ->leftJoin('pp.revisions', 'ppr')
            ->addSelect('ppr')
            ->leftJoin('ppr.meta', 'pprm')
            ->addSelect('pprm')
            ->getQuery()
            ->getResult()
        ;

        foreach ($revisions as $revision) {
            $locale = $revision->getLocale();
            $path = $revision->getMeta()->getSlug();
            $parentPage = $revision->getParentPage();
            while ($parentPage !== null) {
                $parentRevision = $parentPage->getActiveRevision($locale);
                $path = $parentRevision->getMeta()->getSlug() . '/' . $path;
                $parentPage = $parentRevision->getParentPage();
            }
            $pagesRoutes->add(
                $revision->getRouteName(),
                new Route(
                     $path,
                    [
                        '_controller' => PageController::class,
                        '_locale' => $locale->value,
                        'revision' => $revision->getId(),
                    ],
                    [
                         '_locale' => $locale->value,
                    ]
                )
            );
        }

        if ($_ENV['SITE_MULTILINGUAL'] === 'true') {
            $pagesRoutes->addPrefix('{_locale}/');
            $pagesRoutes->add(
                LocaleRedirectController::ROUTE_MULTILINGUAL,
                new Route(
                    '/{_locale}/{path}',
                    [
                        '_controller' => LocaleRedirectController::class,
                        'path' => '~',
                    ],
                    [
                        'path' => '.+',
                        'locale' => implode('|', $this->installedLocaleRepository->findForWebsite()),
                    ],
                ),
                -1
            );
            $pagesRoutes->add(
                LocaleRedirectController::ROUTE_MONOLINGUAL,
                new Route(
                    '/{path}',
                    [
                        '_controller' => LocaleRedirectController::class,
                        'path' => '',
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
