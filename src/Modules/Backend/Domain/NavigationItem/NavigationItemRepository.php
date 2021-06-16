<?php

namespace ForkCMS\Modules\Backend\Domain\NavigationItem;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        parent::__construct($registry, NavigationItem::class);
    }

    public function remove(NavigationItem $navigationItem): void
    {
        $this->getEntityManager()->remove($navigationItem);
    }

    public function save(NavigationItem $navigationItem): void
    {
        if (!$this->getEntityManager()->contains($navigationItem)) {
            $this->getEntityManager()->persist($navigationItem);
        }

        $this->getEntityManager()->flush();
    }
}
