<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\Navigation;

use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItem;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItemRepository;
use Psr\Cache\CacheItemPoolInterface;

final readonly class NavigationCache
{
    private const string CACHE_KEY = 'backend_navigation';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private NavigationItemRepository $navigationItemRepository,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function get(bool $invalidateCache = false): array
    {
        $cachedNavigation = $this->cache->getItem(self::CACHE_KEY);
        if (!$invalidateCache && $cachedNavigation->isHit()) {
            return $cachedNavigation->get();
        }

        $navigation = $this->buildNavigationTree();
        $cachedNavigation->set($navigation);
        $this->cache->save($cachedNavigation);

        return $navigation;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildNavigationTree(): array
    {
        $navigationItems = $this->navigationItemRepository->findChildrenForParentId(null);

        if (count($navigationItems) === 0) {
            return [];
        }

        return array_filter(
            array_map(
                function (NavigationItem $navigationItem) {
                    return $this->buildNavigationItem($navigationItem);
                },
                $navigationItems
            )
        );
    }

    /** @return array<string, mixed>|null */
    private function buildNavigationItem(NavigationItem $navigationItemEntity): ?array
    {
        if (!$navigationItemEntity->visibleInNavigationMenu) {
            return null;
        }

        $navigationItem = [
            'slug' => $navigationItemEntity->getFirstAvailableSlug(),
            'label' => $navigationItemEntity->label,
            'selected_for' => array_filter(
                $navigationItemEntity->children
                    ->filter(
                        static fn (NavigationItem $item): bool => !$item->visibleInNavigationMenu
                                                                  && $item->slug instanceof ActionSlug
                    )
                    ->map(static fn (NavigationItem $item): ?string => $item->slug?->getSlug())
                    ->toArray()
            ),
            'children' => array_filter(
                $navigationItemEntity->children->map(
                    function (NavigationItem $navigationItem) {
                        return $this->buildNavigationItem($navigationItem);
                    }
                )->toArray()
            ),
        ];
        $slug = $navigationItemEntity->slug;
        if ($slug instanceof ActionSlug) {
            $navigationItem['selected_for'][] = $slug->getSlug();
        }

        return $navigationItem;
    }
}
