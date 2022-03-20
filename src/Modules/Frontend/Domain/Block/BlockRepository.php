<?php

namespace ForkCMS\Modules\Frontend\Domain\Block;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Sortable\Entity\Repository\SortableRepository;
use LogicException;

/**
 * @method Block|null find($id, $lockMode = null, $lockVersion = null)
 * @method Block|null findOneBy(array $criteria, array $orderBy = null)
 * @method Block[] findAll()
 * @method Block[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlockRepository extends SortableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        $manager = $registry->getManagerForClass(Block::class);

        if ($manager === null) {
            throw new LogicException(
                sprintf(
                    'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                    Block::class
                )
            );
        }

        parent::__construct(
            $manager,
            $manager->getClassMetadata(Block::class)
        );
    }

    public function save(Block $block): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($block);
        $entityManager->flush();
    }

    public function remove(Block $block): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($block);
        $entityManager->flush();
    }

    /**
     * @return Block[]
     */
    public function getWidgets(): array
    {
        return $this
            ->createQueryBuilder('b')
            ->where('b.type = :type')
            ->andWhere('b.hidden = :hidden')
            ->setParameters(
                [
                    'type' => Type::WIDGET,
                    'hidden' => false,
                ]
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Block[]
     */
    public function getActions(): array
    {
        return $this
            ->createQueryBuilder('b')
            ->where('b.type = :type')
            ->andWhere('b.hidden = :hidden')
            ->setParameters(
                [
                    'type' => Type::ACTION,
                    'hidden' => false,
                ]
            )
            ->getQuery()
            ->getResult();
    }
}
