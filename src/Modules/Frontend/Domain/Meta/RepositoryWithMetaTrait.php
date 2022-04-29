<?php

namespace ForkCMS\Modules\Frontend\Domain\Meta;

use Doctrine\ORM\QueryBuilder;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @template T
 */
trait RepositoryWithMetaTrait
{
    private static string $metaSubjectIdField = 'id';

    /** @param T $subject */
    public function slugify(string $string, object $subject, Locale|null $locale = null): string
    {
        $slugger = new AsciiSlugger(Locale::default()->value);
        $slug = $slugger->slug(mb_strtolower($string))->toString();
        $entityAlias = 's';
        $query = $this->createQueryBuilder($entityAlias)
            ->select('COUNT(s)')
            ->innerJoin('s.meta', 'm')
            ->andWhere('m.slug = :slug')
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug);
        $this->slugifyIdQueryBuilder($query, $subject, $entityAlias);

        if ((int) $query->getQuery()->getSingleScalarResult() == 0) {
            return $slug;
        }

        return $this->slugify($this->addOrIncreaseNumberAtEndOfString($slug), $subject, $locale);
    }

    /** @param T $subject */
    abstract protected function slugifyIdQueryBuilder(QueryBuilder $queryBuilder, object $subject, string $entityAlias): void;

    final protected function addOrIncreaseNumberAtEndOfString(string $string): string
    {
        $chunks = explode('-', $string);
        $last = $chunks[count($chunks) - 1];

        if (ctype_digit($last)) {
            array_pop($chunks);

            // return incremented string
            return implode('-', $chunks) . '-' . ((int) $last + 1);
        }

        return $string . '-1';
    }
}
