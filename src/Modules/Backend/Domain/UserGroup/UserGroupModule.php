<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_group_modules")
 */
class UserGroupModule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Orm\ManyToOne(targetEntity="UserGroup", inversedBy="modules")
     * @Orm\JoinColumn(referencedColumnName="id")
     */
    private UserGroup $userGroup;

    /**
     * @ORM\Column(type="modules__extensions__module__module_name")
     */
    private ModuleName $moduleName;

    public function __construct(UserGroup $userGroup, ModuleName $moduleName)
    {
        $this->userGroup = $userGroup;
        $this->moduleName = $moduleName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getModuleName(): ModuleName
    {
        return $this->moduleName;
    }
}
