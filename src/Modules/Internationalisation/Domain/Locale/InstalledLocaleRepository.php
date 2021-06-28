<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

/**
 * @method InstalledLocale|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstalledLocale|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstalledLocale[] findAll()
 * @method InstalledLocale[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class InstalledLocaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        try {
            parent::__construct($registry, InstalledLocale::class);
        } catch (Throwable $throwable) {
            if (!empty($_ENV['FORK_DATABASE_HOST'])) {
                throw $throwable; // needed during the installer
            }
        }
    }

    public function remove(InstalledLocale $installedLocale): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($installedLocale);
        $entityManager->flush();
    }

    public function save(InstalledLocale $installedLocale): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($installedLocale);
        $entityManager->flush();
    }
}
