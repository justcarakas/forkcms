<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_group_actions")
 */
class UserGroupAction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Orm\ManyToOne(targetEntity="UserGroup", inversedBy="actions")
     * @Orm\JoinColumn(referencedColumnName="id")
     */
    private UserGroup $userGroup;

    /**
     * @ORM\Embedded
     */
    private ModuleAction $moduleAction;

    public function __construct(UserGroup $userGroup, ModuleAction $moduleAction)
    {
        $this->userGroup = $userGroup;
        $this->moduleAction = $moduleAction;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getModuleAction(): ModuleAction
    {
        return $this->moduleAction;
    }
}
