<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItem;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Throwable;

/**
 * @method Translation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Translation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Translation[] findAll()
 * @method Translation[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class TranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private ManagerRegistry $managerRegistry)
    {
        try {
            parent::__construct($registry, Translation::class);
        } catch (Throwable $throwable) {
            if (!empty($_ENV['FORK_DATABASE_HOST'])) {
                throw $throwable; // needed during the installer
            }
        }
    }

    public function remove(Translation $translation): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($translation);
        $entityManager->flush();
    }

    public function save(Translation $translation): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($translation);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $uniqueConstraintViolationException) {
            $this->managerRegistry->resetManager();
            throw $uniqueConstraintViolationException;
        }
    }
}
