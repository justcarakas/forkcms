<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Command;

final readonly class DeleteUserGroup
{
    public function __construct(public int $userGroupId)
    {
    }
}
