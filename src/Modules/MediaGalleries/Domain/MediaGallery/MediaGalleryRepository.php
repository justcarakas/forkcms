<?php

namespace ForkCMS\Modules\MediaGalleries\Domain\MediaGallery;

use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\Exception\MediaGalleryNotFound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MediaGallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaGallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaGallery[] findAll()
 * @method MediaGallery[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method MediaGallery|null findOneByTitle(string $title)
 */
final class MediaGalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaGallery::class);
    }

    /**
     * We don't flush here, see http://disq.us/p/okjc6b
     */
    public function add(MediaGallery $mediaGallery): void
    {
        $this->getEntityManager()->persist($mediaGallery);
    }

    public function existsByTitle(string $title, string $ignoreMediaGalleryId = null): bool
    {
        $mediaGallery = $this->findOneByTitle($title);

        return ($mediaGallery instanceof MediaGallery) ? ($mediaGallery->getId() !== $ignoreMediaGalleryId) : false;
    }

    public function findOneById(string $id = null): MediaGallery
    {
        if ($id === null) {
            throw MediaGalleryNotFound::forEmptyId();
        }

        /** @var MediaGallery|null $mediaGallery */
        $mediaGallery = $this->find($id);

        if ($mediaGallery === null) {
            throw MediaGalleryNotFound::forId($id);
        }

        return $mediaGallery;
    }

    /**
     * We don't flush here, see http://disq.us/p/okjc6b
     */
    public function remove(MediaGallery $mediaGallery): void
    {
        $this->getEntityManager()->remove($mediaGallery);
    }
}
