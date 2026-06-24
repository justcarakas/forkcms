<?php

namespace ForkCMS\Modules\Pages\Domain\Page;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Page[] findAll()
 * @method Page[] findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Page>
 */
final class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly NavigationBuilder $navigationBuilder)
    {
        parent::__construct($managerRegistry, Page::class);
    }

    public function save(Page $page): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($page);
        $entityManager->flush();
        $this->navigationBuilder->clearNavigationCache();
    }

    public function remove(Page $page): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($page);
        $entityManager->flush();
        $this->navigationBuilder->clearNavigationCache();
    }
}
