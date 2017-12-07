<?php

namespace Backend\Modules\Locale\Domain\Translation;

use Doctrine\ORM\EntityRepository;

final class TranslationRepository extends EntityRepository
{
    public function add(Translation $translation): void
    {
        $this->getEntityManager()->persist($translation);
        $this->getEntityManager()->flush();
    }

    public function remove(Translation $translation): void
    {
        $this->getEntityManager()->remove($translation);
        $this->getEntityManager()->flush();
    }
}
