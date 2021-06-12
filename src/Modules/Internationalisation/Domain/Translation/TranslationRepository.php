<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Translation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Translation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Translation[] findAll()
 * @method Translation[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class TranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function remove(Translation $translation): void
    {
        $this->getEntityManager()->remove($translation);
    }

    public function save(Translation $translation): void
    {
        if (!$this->getEntityManager()->contains($translation)) {
            $this->getEntityManager()->persist($translation);
        }

        $this->getEntityManager()->flush();
    }
}
