<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Pages\Domain\Revision;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Meta\MetaCallbackService;
use ForkCMS\Modules\Frontend\Domain\Meta\RepositoryWithMetaTrait;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\NavigationBuilder;
use ForkCMS\Modules\Pages\Domain\Page\Page;

/**
 * @method Revision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Revision|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Revision[] findAll()
 * @method Revision[] findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null) // phpcs:ignore Generic.Files.LineLength.TooLong
 * @extends ServiceEntityRepository<Revision>
 */
final class RevisionRepository extends ServiceEntityRepository implements MetaCallbackService
{
    /** @use RepositoryWithMetaTrait<Revision> */
    use RepositoryWithMetaTrait;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NavigationBuilder $navigationBuilder,
    ) {
        parent::__construct($managerRegistry, Revision::class);
    }

    public function save(Revision $revision): void
    {
        $entityManager = $this->getEntityManager();

        $revision->meta->slug = $this->slugify($revision->title, $revision, $revision->getLocale());
        $entityManager->persist($revision);
        $entityManager->flush();
        if ($revision->page->id === Page::PAGE_ID_HOME) {
            $revision->meta->slug = '';
            $entityManager->flush();
        }
        $this->navigationBuilder->clearNavigationCache();
    }

    public function remove(Revision $revision): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($revision);
        $entityManager->flush();
        $this->navigationBuilder->clearNavigationCache();
    }

    /** @TODO verify if used */
    public function generateSlug(string $slug, Locale $locale, ?int $revisionId): string
    {
        if ($revisionId === null) {
            return $this->slugify($slug, null, $locale);
        }

        return $this->slugify($slug, $this->findOneBy(['id' => $revisionId, 'locale' => $locale->value]), $locale);
    }

    protected function slugifyIdQueryBuilder(
        QueryBuilder $queryBuilder,
        ?object $subject,
        Locale $locale,
        string $entityAlias
    ): void {
        $queryBuilder
            ->andWhere($entityAlias . '.locale = :locale')
            ->setParameter('locale', ($subject?->getLocale() ?? $locale)->value);
        if ($subject !== null) {
            if ($subject->page->hasId()) {
                $queryBuilder
                    ->andWhere($entityAlias . '.page != :page')
                    ->setParameter('page', $subject->page);
            }
            if ($subject->parentPage === null) {
                $queryBuilder
                    ->andWhere($entityAlias . '.parentPage IS NULL');
            } else {
                $queryBuilder
                    ->andWhere($entityAlias . '.parentPage = :parentPage')
                    ->setParameter('parentPage', $subject->parentPage);
            }
        }
    }

    /** @return Revision[] */
    public function findRevisionsForFrontendBlock(Block $block, bool $onlyActive = true): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->from(Revision::class, 'r')
            ->select('r')
            ->innerJoin('r.blocks', 'rb')
            ->innerJoin('rb.block', 'b')
            ->andWhere('b.id = :blockId')
            ->setParameter('blockId', $block->id);

        if ($onlyActive) {
            $queryBuilder->andWhere('r.archivedOn IS NULL');
        }
        $revisions = $queryBuilder->getQuery()->disableResultCache()->getResult();
        $this->getEntityManager()->getFilters()->enable('softdeleteable');

        return $revisions;
    }

    public function deleteFrontendBlockFromRevisions(Block $block): void
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');

        foreach ($this->findRevisionsForFrontendBlock($block, onlyActive: false) as $revision) {
            foreach ($revision->blocks as $revisionBlock) {
                $revisionBlockFrontendBlock = $revisionBlock->block;
                if ($revisionBlockFrontendBlock !== null && $revisionBlockFrontendBlock->id === $block->id) {
                    $revision->removeBlock($revisionBlock);
                }
            }
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->getFilters()->enable('softdeleteable');
    }
}
