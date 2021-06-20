<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

/**
 * @method UserGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGroup[] findAll()
 * @method UserGroup[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class UserGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroup::class);
    }

    public function save(UserGroup $userGroup): void
    {
        $this->getEntityManager()->persist($userGroup);
        $this->getEntityManager()->flush();
    }

    public function remove(UserGroup $userGroup): void
    {
        if ($userGroup->getId() === UserGroup::ADMIN_GROUP_ID) {
            throw new InvalidArgumentException('Deleting the admin group is not allowed');
        }

        $this->getEntityManager()->remove($userGroup);
    }


    public function getAdminUserGroup(): UserGroup
    {
        $adminUserGroup = $this->findOneBy(['id' => UserGroup::ADMIN_GROUP_ID]);
        if (!$adminUserGroup instanceof UserGroup) {
            $adminUserGroup = new UserGroup('Admin');
            $this->save($adminUserGroup);
        }

        return $adminUserGroup;
    }
}
