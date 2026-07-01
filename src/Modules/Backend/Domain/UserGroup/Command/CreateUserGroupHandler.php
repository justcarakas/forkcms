<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\UserGroup\Event\UserGroupCreatedEvent;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class CreateUserGroupHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserGroupRepository $userGroupRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateUserGroup $createUserGroup): void
    {
        $createUserGroup->setEntity(UserGroup::fromDataTransferObject($createUserGroup));
        $this->userGroupRepository->save($createUserGroup->getEntity());
        $this->eventDispatcher->dispatch(new UserGroupCreatedEvent($createUserGroup->getEntity()));
    }
}
