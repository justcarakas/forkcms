<?php

namespace ForkCMS\Modules\Pages\Domain\Revision;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Frontend\Domain\Meta\RepositoryWithMetaTrait;
use ForkCMS\Modules\Pages\Domain\Page\Page;

/**
 * @method Revision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Revision|null findOneBy(array $criteria, array $orderBy = null)
 * @method Revision[] findAll()
 * @method Revision[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Revision>
 * @implements RepositoryWithMetaTrait<Revision>
 */
final class RevisionRepository extends ServiceEntityRepository
{
    use RepositoryWithMetaTrait;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Revision::class);
    }

    public function save(Revision $revision): void
    {
        $entityManager = $this->getEntityManager();

        $revision->getMeta()->setSlug($this->slugify($revision->getTitle(), $revision, $revision->getLocale()));
        $entityManager->persist($revision);
        $entityManager->flush();
        if ($revision->getPage()->getId() === Page::PAGE_ID_HOME) {
            $revision->getMeta()->setSlug('');
            $entityManager->flush();
        }
    }

    public function remove(Revision $revision): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($revision);
        $entityManager->flush();
    }

    protected function slugifyIdQueryBuilder(
        QueryBuilder $queryBuilder,
        object $subject,
        string $entityAlias
    ): void {
        $queryBuilder
            ->andWhere($entityAlias . '.locale = :locale')
            ->setParameter('locale', $subject->getLocale()->value);
        if ($subject->getPage()->hasId()) {
            $queryBuilder
                ->andWhere($entityAlias . '.page != :page')
                ->setParameter('page', $subject->getPage());
        }
        if ($subject->getParentPage() === null) {
            $queryBuilder
                ->andWhere($entityAlias . '.parentPage IS NULL');
        } else {
            $queryBuilder
                ->andWhere($entityAlias . '.parentPage = :parentPage')
                ->setParameter('parentPage', $subject->getParentPage());
        }
    }
}
