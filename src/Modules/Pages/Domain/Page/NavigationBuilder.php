<?php

namespace ForkCMS\Modules\Pages\Domain\Page;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Revision\MenuType;
use Symfony\Contracts\Cache\CacheInterface;

final class NavigationBuilder
{
    public const GROUPED_PAGES_CACHE_KEY = 'pages_grouped_';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
        private readonly ModuleSettings $moduleSettings
    ) {
    }

    private static function getSubTree(MenuType $type, array $groupedPages, int $parentId = 0): ?array
    {
        /** @var Page[] $subPages */
        $subPages = $groupedPages[$type->value][$parentId] ?? null;

        if ($subPages === null || count($subPages) === 0) {
            return null;
        }

        $subTree = [];
        foreach ($subPages as $page) {
            $pageTreeType = $page->getPageTreeType();
            $pageId = $page->getId();
            $subTree[$pageId] = [
                'attr' => [
                    'rel' => $pageTreeType,
                    'data-jstree' => '{"type":"' . $pageTreeType . '"}',
                ],
                'page' => $page,
                'children' => self::getSubtree($type, $groupedPages, $pageId),
            ];
        }

        return $subTree;
    }

    public function clearNavigationCache(): void
    {
        foreach (Locale::cases() as $locale) {
            $this->cache->delete(self::GROUPED_PAGES_CACHE_KEY . $locale->value);
        }
    }

    public function getTree(Locale $locale): array
    {
        $tree = [];
        $groupedPages = $this->getGroupedPages($locale);
        foreach (MenuType::cases() as $type) {
            if (
                $type === MenuType::META
                && !$this->moduleSettings->get(ModuleName::fromString('Pages'), 'meta_navigation', false)
            ) {
                continue;
            }

            $tree[$type->value] = [
                'name' => 'main',
                'label' => $type,
                'pages' => self::getSubTree($type, $groupedPages),
            ];
        }

        return $tree;
    }

    private function getGroupedPages(Locale $locale): array
    {
        $cache = $this->cache->getItem(self::GROUPED_PAGES_CACHE_KEY . $locale->value);
        if ($cache->isHit()) {
            return $cache->get();
        }

        /** @var Page[] $pages */
        $pages = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Page::class, 'p')
            ->setParameter('locale', $locale->value)
            ->innerJoin(
                'p.revisions',
                'pr',
                Join::WITH,
                'pr.locale = :locale AND pr.isArchived IS NULL'
            )
            ->addSelect('pr')
            ->leftJoin('pr.meta', 'prm')
            ->addSelect('prm')
            ->leftJoin(
                'p.childRevisions',
                'cr',
                Join::WITH,
                'cr.locale = :locale AND cr.isArchived IS NULL'
            )
            ->addSelect('cr')
            ->leftJoin('pr.page', 'crp')
            ->addSelect('crp')
            ->getQuery()
            ->getResult();

        $groupedPages = [];
        foreach ($pages as $page) {
            $revision = $page->getActiveRevision($locale);
            $type = $revision->getType()->value;
            $pageId = $page->getId();
            $groupedPages[$type][$revision->getParentPage()?->getId() ?? 0][$pageId] = $page;
        }

        $cache->set($groupedPages);
        $this->cache->save($cache);

        return $groupedPages;
    }
}
