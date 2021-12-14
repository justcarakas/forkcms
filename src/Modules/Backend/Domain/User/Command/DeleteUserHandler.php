<?php

namespace ForkCMS\Modules\Backend\Domain\User\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;

final class DeleteUserHandler implements CommandHandlerInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(DeleteUser $deleteUser): void
    {
        $this->userRepository->remove($this->userRepository->find($deleteUser->getUserId()));
    }
}
