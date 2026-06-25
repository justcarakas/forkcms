<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;
use ForkCMS\Modules\Backend\Domain\UserGroup\Event\UserGroupDeletedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DeleteUserGroupHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserGroupRepository $userGroupRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(DeleteUserGroup $deleteUserGroup): void
    {
        $userGroup = Ensure::isNotNull(
            $this->userGroupRepository->find($deleteUserGroup->getUserGroupId()),
            'UserGroup not found'
        );
        $this->userGroupRepository->remove($userGroup);
        $this->eventDispatcher->dispatch(new UserGroupDeletedEvent($userGroup));
    }
}
