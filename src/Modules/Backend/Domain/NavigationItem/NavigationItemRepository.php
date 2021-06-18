<?php

namespace ForkCMS\Modules\Backend\Domain\NavigationItem;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Throwable;

/**
 * @method NavigationItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method NavigationItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method NavigationItem[] findAll()
 * @method NavigationItem[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class NavigationItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        try {
            parent::__construct($registry, NavigationItem::class);
        } catch (Throwable $throwable) {
            if (getenv('FORK_DATABASE_HOST')) {
                throw $throwable; // needed during the installer
            }
        }
    }

    public function remove(NavigationItem $navigationItem): void
    {
        $this->getEntityManager()->remove($navigationItem);
    }

    public function save(NavigationItem $navigationItem): void
    {
        $this->getEntityManager()->persist($navigationItem);
        $this->getEntityManager()->flush();
    }

    public function findUnique(
        TranslationKey $label,
        ?ActionSlug $slug,
        ?NavigationItem $parent
    ): ?NavigationItem {
        return $this->findOneBy(
            [
                'label.type' => $label->getType(),
                'label.name' => $label->getName(),
                'slug' => $slug,
                'parent' => $parent,
            ]
        );
    }
}
