<?php

namespace ForkCMS\Modules\Backend\Domain\NavigationItem;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Generator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

/**
 * @method NavigationItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method NavigationItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method NavigationItem[] findAll()
 * @method NavigationItem[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class NavigationItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private AuthorizationCheckerInterface $authorizationChecker)
    {
        try {
            parent::__construct($registry, NavigationItem::class);
        } catch (Throwable $throwable) {
            if (!empty($_ENV['FORK_DATABASE_HOST'])) {
                throw $throwable; // needed during the installer
            }
        }
    }

    public function remove(NavigationItem $navigationItem): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($navigationItem);
        $entityManager->flush();
    }

    public function save(NavigationItem $navigationItem): void
    {
        $this->getEntityManager()->persist($navigationItem);
        $this->getEntityManager()->flush();
    }

    public function findUnique(
        TranslationKey $label,
        ?ActionSlug $slug,
        ?NavigationItem $parent
    ): ?NavigationItem {
        return $this->findOneBy(
            [
                'label.type' => $label->getType(),
                'label.name' => $label->getName(),
                'slug' => $slug,
                'parent' => $parent,
            ]
        );
    }

    public function findFirstWithSlugForUser(User $user): NavigationItem
    {
        $sortedNavigationItems = $this->findSortedNavigationItems();

        foreach ($sortedNavigationItems as $navigationItem) {
            $accessibleNavigationItem = $this->findAccessibleNavigationItemForUser($navigationItem, $user);
            if ($accessibleNavigationItem instanceof NavigationItem) {
                return $accessibleNavigationItem;
            }
        }

        throw new AccessDeniedException('No accessible navigation item found');
    }

    private function findAccessibleNavigationItemForUser(NavigationItem $navigationItem, User $user): ?NavigationItem
    {
        if ($navigationItem->getModuleAction() instanceof ModuleAction
            && $this->authorizationChecker->isGranted($navigationItem->getModuleAction()->asRole())) {
            return $navigationItem;
        }

        foreach ($navigationItem->getChildren() as $childNavigationItem) {
            $result = $this->findAccessibleNavigationItemForUser($childNavigationItem, $user);
            if ($result instanceof NavigationItem) {
                return $result;
            }
        }

        return null;
    }

    /** @return NavigationItem[] */
    public function findSortedNavigationItems(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.parent IS NULL')
            ->orderBy('n.sequence, c1.sequence, c2.sequence')
            ->leftJoin('n.children', 'c1')
            ->addSelect('c1')
            ->leftJoin('c1.children', 'c2')
            ->addSelect('c2')
            ->getQuery()
            ->getResult();
    }
}
