<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;

final class CreateUserGroupHandler implements CommandHandlerInterface
{
    public function __construct(private UserGroupRepository $userGroupRepository)
    {
    }

    public function __invoke(CreateUserGroup $createUserGroup): void
    {
        $this->userGroupRepository->save(UserGroup::fromDataTransferObject($createUserGroup));
    }
}
