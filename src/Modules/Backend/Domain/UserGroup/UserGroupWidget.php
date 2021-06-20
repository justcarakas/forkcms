<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_group_widgets")
 */
class UserGroupWidget
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Orm\ManyToOne(targetEntity="UserGroup", inversedBy="widgets")
     * @Orm\JoinColumn(referencedColumnName="id")
     */
    private UserGroup $userGroup;

    /**
     * @ORM\Embedded
     */
    private ModuleWidget $moduleWidget;

    public function __construct(int $id, UserGroup $userGroup, ModuleWidget $moduleWidget)
    {
        $this->id = $id;
        $this->userGroup = $userGroup;
        $this->moduleWidget = $moduleWidget;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getModuleWidget(): ModuleWidget
    {
        return $this->moduleWidget;
    }
}
