<?php

namespace ForkCMS\Core\Domain\Module;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Core\Installer\Domain\Installer\InstallableModule;
use Traversable;

/**
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[] findAll()
 * @method Module[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ModuleRepository extends ServiceEntityRepository
{
    /** @var Traversable|InstallableModule[] */
    private Traversable $availableModules;

    public function __construct(Traversable $availableModules, ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);

        $this->availableModules = $availableModules;
    }

    public function add(Module $module): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($module);
        $entityManager->flush();
    }

    /** @return  Traversable|ModuleName[] */
    public function getAvailableModules(): Traversable
    {
        return $this->availableModules;
    }
}
