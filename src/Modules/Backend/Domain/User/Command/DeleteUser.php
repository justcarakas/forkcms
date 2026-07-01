<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\User\Command;

final readonly class DeleteUser
{
    public function __construct(public int $userId)
    {
    }
}
