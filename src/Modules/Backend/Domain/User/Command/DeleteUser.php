<?php

namespace ForkCMS\Modules\Backend\Domain\User\Command;

final readonly class DeleteUser
{
    public function __construct(public int $userId)
    {
    }
}
