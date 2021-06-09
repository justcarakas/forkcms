<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        parent::__construct($registry, Module::class);
    }

    public function save(Module $module): void
    {
        $entityManager = $this->getEntityManager();
        if (!$entityManager->contains($module)) {
            $entityManager->persist($module);
        }
        $entityManager->flush();
    }
}
