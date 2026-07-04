<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Block\BlockRepository;
use ForkCMS\Modules\Frontend\Domain\Block\Event\IsBlockInUseEvent;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @method ContentBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContentBlock|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method ContentBlock[] findAll()
 * @method ContentBlock[] findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null) // phpcs:ignore Generic.Files.LineLength.TooLong
 * @extends ServiceEntityRepository<ContentBlock>
 */
final class ContentBlockRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly BlockRepository $blockRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($managerRegistry, ContentBlock::class);
    }

    public function saveRevision(Revision $revision): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($revision);
        $entityManager->flush();
    }

    /** Removes the content block and all of its revisions. */
    public function remove(ContentBlock $contentBlock): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($contentBlock);
        $entityManager->flush();

        $this->blockRepository->remove($contentBlock->widget);
    }

    public function findForWidget(Block $widget): ContentBlock
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.revisions', 'r', 'WITH', 'r.archivedOn IS NULL AND c.widget = :widget')
            ->setParameter('widget', $widget)
            ->addSelect('r')
            ->innerJoin('c.widget', 'w')
            ->addSelect('w')
            ->getQuery()
            ->getSingleResult();
    }

    public function isContentBlockInUse(ContentBlock $contentBlock): bool
    {
        $isContentBlockInUseEvent = new IsBlockInUseEvent($contentBlock->widget);
        $this->eventDispatcher->dispatch($isContentBlockInUseEvent);

        return $isContentBlockInUseEvent->inUse;
    }
}
