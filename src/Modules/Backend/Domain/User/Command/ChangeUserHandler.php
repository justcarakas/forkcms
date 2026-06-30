<?php

namespace ForkCMS\Modules\Backend\Domain\User\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\User\Event\UserChangedEvent;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class ChangeUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(ChangeUser $changeUser): void
    {
        $user = User::fromDataTransferObject($changeUser);
        $user->hashPassword($this->passwordHasher);
        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch(new UserChangedEvent($user));
    }
}
