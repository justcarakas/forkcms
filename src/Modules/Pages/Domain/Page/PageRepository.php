<?php

namespace ForkCMS\Modules\Pages\Domain\Page;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Revision\MenuType;
use ForkCMS\Modules\Pages\Domain\Revision\Type;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[] findAll()
 * @method Page[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Page>
 */
final class PageRepository extends ServiceEntityRepository
{
    public const GROUPED_PAGES_CACHE_KEY = 'pages_grouped_';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly CacheInterface $cache,
    ) {
        parent::__construct($managerRegistry, Page::class);
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

    public function save(Page $page): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($page);
        $entityManager->flush();
        $this->clearNavigationCache();
    }

    public function clearNavigationCache(): void
    {
        foreach (Locale::cases() as $locale) {
            $this->cache->delete(self::GROUPED_PAGES_CACHE_KEY . $locale->value);
        }
    }

    public function remove(Page $page): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($page);
        $entityManager->flush();
        $this->clearNavigationCache();
    }

    public function getTree(Locale $locale): array
    {
        $tree = [];
        $groupedPages = $this->getGroupedPages($locale);
        foreach (MenuType::cases() as $type) {
            $tree[$type->value] = [
                'name' => 'main',
                'label' => $type,
                'pages' => self::getSubTree($type, $groupedPages),
            ];
        }

        return $tree;

        $tree['main'] = [
            'name' => 'main',
            'label' => 'MainNavigation',
            'pages' => self::getSubtree(Type::page(), $navigation, 0),
        ];
        if (BackendModel::get(ModuleSettingRepository::class)->get('Pages', 'meta_navigation', false)) {
            $tree['meta'] = [
                'name' => 'meta',
                'label' => 'Meta',
                'pages' => self::getSubtree(Type::meta(), $navigation, 0),
            ];
        }
        $tree['footer'] = [
            'name' => 'footer',
            'label' => 'Footer',
            'pages' => self::getSubtree(Type::footer(), $navigation, 0),
        ];
        $tree['root'] = [
            'name' => 'root',
            'label' => 'Root',
            'pages' => self::getSubtree(Type::root(), $navigation, 0),
        ];

        return $tree;
    }

    private function getGroupedPages(Locale $locale): array
    {
        $cache = $this->cache->getItem(self::GROUPED_PAGES_CACHE_KEY . $locale->value);
        if ($cache->isHit()) {
            return $cache->get();
        }

        /** @var Page[] $pages */
        $pages = $this->createQueryBuilder('p')
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
