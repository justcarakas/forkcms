<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;

final class DeleteUserGroupHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserGroupRepository $userGroupRepository)
    {
    }

    public function __invoke(DeleteUserGroup $deleteUserGroup): void
    {
        $this->userGroupRepository->remove($this->userGroupRepository->find($deleteUserGroup->getUserGroupId()));
    }
}
