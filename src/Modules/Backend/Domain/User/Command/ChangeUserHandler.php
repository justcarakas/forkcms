<?php

namespace ForkCMS\Modules\Backend\Domain\User\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;

final class ChangeUserHandler implements CommandHandlerInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(ChangeUser $changeUser): void
    {
        $this->userRepository->save(User::fromDataTransferObject($changeUser));
    }
}
