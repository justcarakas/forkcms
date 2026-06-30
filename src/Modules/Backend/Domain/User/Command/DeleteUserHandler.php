<?php

namespace ForkCMS\Modules\Backend\Domain\User\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;
use ForkCMS\Modules\Backend\Domain\User\Event\UserDeletedEvent;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class DeleteUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(DeleteUser $deleteUser): void
    {
        $user = $this->userRepository->find($deleteUser->userId);
        if ($user === null) {
            throw new InvalidArgumentException('User not found');
        }
        $this->userRepository->remove($user);
        $this->eventDispatcher->dispatch(new UserDeletedEvent($user));
    }
}
