<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;

final class ChangeUserGroupHandler implements CommandHandlerInterface
{
    public function __construct(private UserGroupRepository $userGroupRepository)
    {
    }

    public function __invoke(ChangeUserGroup $changeUserGroup): void
    {
        $this->userGroupRepository->save(UserGroup::fromDataTransferObject($changeUserGroup));
    }
}
