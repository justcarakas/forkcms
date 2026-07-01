<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\User\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class CreateUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateUser $createUser): void
    {
        $user = User::fromDataTransferObject($createUser);
        $user->hashPassword($this->passwordHasher);
        $this->userRepository->save($user);
        $createUser->setEntity($user);
        $this->eventDispatcher->dispatch($user);
    }
}
