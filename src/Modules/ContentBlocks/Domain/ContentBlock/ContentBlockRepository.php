<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Frontend\Domain\Block\BlockRepository;
use ForkCMS\Modules\Frontend\Domain\Block\Event\IsBlockInUseEvent;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @method ContentBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContentBlock|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method ContentBlock[]    findAll()
 * @method ContentBlock[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null) // phpcs:ignore Generic.Files.LineLength.TooLong
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

    public function save(ContentBlock $contentBlock): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($contentBlock);
        $entityManager->flush();

        if ($contentBlock->status !== Status::ARCHIVED) {
            $this->updateWidget($contentBlock);
        }
    }

    public function remove(ContentBlock $contentBlock): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($contentBlock);
        $entityManager->flush();
    }

    /** @param ContentBlock[] $contentBlocks */
    public function removeMultiple(array $contentBlocks): void
    {
        $widgets = [];
        foreach ($contentBlocks as $contentBlock) {
            $widget = $contentBlock->widget;
            $widgets[$widget->id] = $widget;
        }

        $entityManager = $this->getEntityManager();
        foreach ($contentBlocks as $contentBlock) {
            $entityManager->remove($contentBlock);
        }

        foreach ($widgets as $widget) {
            $this->blockRepository->remove($widget);
        }

        $entityManager->flush();
    }

    /** @return ContentBlock[] */
    public function getVersionsForRevisionId(int $revisionId): array
    {
        $contentBlock = $this->findOneby(['revisionId' => $revisionId]);

        if ($contentBlock === null) {
            return [];
        }

        return $this->findBy(['id' => $contentBlock->id, 'locale' => $contentBlock->locale]);
    }

    public function getNextIdForLocale(Locale $locale): int
    {
        return (int) $this->getEntityManager()
                ->createQueryBuilder()
                ->select('MAX(cb.id) as id')
                ->from(ContentBlock::class, 'cb')
                ->where('cb.locale = :locale')
                ->setParameter('locale', $locale)
                ->getQuery()
                ->getSingleScalarResult() + 1;
    }

    public function findForIdAndLocale(int $id, Locale $locale): ?ContentBlock
    {
        return $this->createQueryBuilder('cb')
            ->andWhere('cb.id = :id')
            ->andWhere('cb.status = :active')
            ->andWhere('cb.locale = :locale')
            ->andWhere('cb.isHidden = :false')
            ->setParameter('id', $id)
            ->setParameter('active', Status::ACTIVE)
            ->setParameter('locale', $locale)
            ->setParameter('false', false)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function updateWidget(ContentBlock $contentBlock): void
    {
        $block = $contentBlock->widget;
        $block->getSettings()->add([
            'label' => $contentBlock->title,
            'content_block_id' => $contentBlock->id,
        ]);
        if ($contentBlock->isHidden) {
            $block->hide();
        } else {
            $block->show();
        }

        $this->blockRepository->save($block);
    }

    /**
     * @return array<ContentBlock>
     */
    public function getRevisionsForContentBlock(ContentBlock $contentBlock): array
    {
        return $this->createQueryBuilder('cb')
            ->andWhere('cb.id = :id')
            ->andWhere('cb.locale = :locale')
            ->andWhere('cb.status = :archived')
            ->addOrderBy('cb.updatedOn', 'ASC')
            ->setParameter('id', $contentBlock->id)
            ->setParameter('locale', $contentBlock->locale)
            ->setParameter('archived', Status::ARCHIVED)
            ->getQuery()
            ->getResult();
    }

    public function isContentBlockInUse(ContentBlock $contentBlock): bool
    {
        $isContentBlockInUseEvent = new IsBlockInUseEvent($contentBlock->widget);
        $this->eventDispatcher->dispatch($isContentBlockInUseEvent);

        return $isContentBlockInUseEvent->inUse;
    }
}
