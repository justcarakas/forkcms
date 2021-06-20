<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

/**
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[] findAll()
 * @method Module[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        try {
            parent::__construct($registry, Module::class);
        } catch (Throwable $throwable) {
            if (getenv('FORK_DATABASE_HOST')) {
                throw $throwable;
            }
        }
    }

    public function save(Module $module): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($module);
        $entityManager->flush();
    }
}
