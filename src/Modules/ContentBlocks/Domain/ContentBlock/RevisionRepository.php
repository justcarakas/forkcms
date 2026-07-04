<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;

/**
 * @method Revision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Revision|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Revision[] findAll()
 * @method Revision[] findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null) // phpcs:ignore Generic.Files.LineLength.TooLong
 * @extends ServiceEntityRepository<Revision>
 */
final class RevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Revision::class);
    }

    /**
     * Used by the UniqueDataTransferObject constraint on the title: only an active revision
     * in the current locale counts towards uniqueness, so re-saving a revision with an
     * unchanged title, or reusing a title in another locale, is not flagged as a duplicate.
     *
     * @param array{title: string} $criteria
     *
     * @return Revision[]
     */
    public function findActiveForCurrentLocaleByTitle(array $criteria): array
    {
        $contentBlocks = $this->getEntityManager()->getRepository(ContentBlock::class)
            ->createQueryBuilder('c')
            ->innerJoin('c.revisions', 'r', 'WITH', 'r.archivedOn IS NULL AND r.title = :title')
            ->addSelect('r')
            ->andWhere('c.locale = :locale')
            ->setParameter('title', $criteria['title'])
            ->setParameter('locale', Locale::current())
            ->getQuery()
            ->getResult();

        return array_map(static fn (ContentBlock $contentBlock): Revision => $contentBlock->getActiveRevision(), $contentBlocks);
    }
}
