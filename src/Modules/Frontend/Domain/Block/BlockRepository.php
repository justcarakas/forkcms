<?php

namespace ForkCMS\Modules\Frontend\Domain\Block;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Frontend\Domain\Block\Event\BeforeDeleteBlockEvent;
use Gedmo\Sortable\Entity\Repository\SortableRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends SortableRepository<Block>
 * @method Block|null find($id, $lockMode = null, $lockVersion = null)
 * @method Block|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Block[] findAll()
 * @method Block[] findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null) // phpcs:ignore Generic.Files.LineLength.TooLong
 */
final class BlockRepository extends SortableRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($entityManager, $entityManager->getClassMetadata(Block::class));
    }

    public function save(Block $block): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($block);
        $entityManager->flush();
    }

    public function remove(Block $block): void
    {
        $this->eventDispatcher->dispatch(new BeforeDeleteBlockEvent($block));

        $entityManager = $this->getEntityManager();
        $entityManager->remove($block);
        $entityManager->flush();
    }

    /** @return Block[] */
    public function getWidgets(): array
    {
        return $this
            ->createQueryBuilder('b')
            ->where('b.type = :type')
            ->andWhere('b.hidden = :hidden')
            ->setParameter('type', Type::WIDGET)
            ->setParameter('hidden', false)
            ->getQuery()
            ->getResult();
    }

    /** @return Block[] */
    public function getActions(): array
    {
        return $this
            ->createQueryBuilder('b')
            ->where('b.type = :type')
            ->andWhere('b.hidden = :hidden')
            ->setParameter('type', Type::ACTION)
            ->setParameter('hidden', false)
            ->getQuery()
            ->getResult();
    }

    public function findUnique(
        ModuleBlock $moduleBlock,
        SettingsBag $settings = new SettingsBag()
    ): ?Block {
        $queryBuilder = $this->createQueryBuilder('b')
            ->andWhere('b.block.module = :module')
            ->setParameter('module', $moduleBlock->module->name)
            ->andWhere('b.block.name = :name')
            ->setParameter('name', BlockNameDBALType::prefixedString($moduleBlock->name))
            ->andWhere('b.type = :type')
            ->setParameter('type', $moduleBlock->name->getType()->value)
            ->andWhere('JSON_CONTAINS(b.settings, :settings) = 1')
            ->setParameter('settings', $settings->asJsonString());

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /** @return Block[] */
    public function findAllWidgets(): array
    {
        /* @TODO add check for widgets that have been added but aren't in the database yet */
        return $this->findBy(
            ['type' => Type::WIDGET->value, 'hidden' => false],
            ['type' => Order::Ascending->value, 'position' => Order::Ascending->value]
        );
    }

    /** @return Block[] */
    public function findAllActions(): array
    {
        /* @TODO add check for actions that have been added but aren't in the database yet */
        return $this->findBy(
            ['type' => Type::ACTION->value, 'hidden' => false],
            ['type' => Order::Ascending->value, 'position' => Order::Ascending->value]
        );
    }
}
