<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;

final class CreateUserGroupHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserGroupRepository $userGroupRepository)
    {
    }

    public function __invoke(CreateUserGroup $createUserGroup): void
    {
        $createUserGroup->setEntity(UserGroup::fromDataTransferObject($createUserGroup));
        $this->userGroupRepository->save($createUserGroup->getEntity());
    }
}
