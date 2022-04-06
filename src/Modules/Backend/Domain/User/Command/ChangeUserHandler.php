<?php

namespace ForkCMS\Modules\Backend\Domain\User\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ChangeUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(ChangeUser $changeUser): void
    {
        $user = User::fromDataTransferObject($changeUser);
        $user->hashPassword($this->passwordHasher);
        $this->userRepository->save($user);
    }
}
