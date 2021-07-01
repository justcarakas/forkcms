<?php

namespace ForkCMS\Modules\Backend\Domain\User\Command;

use ForkCMS\Modules\Backend\Domain\User\UserDataTransferObject;

final class CreateUser extends UserDataTransferObject
{
    public function __construct()
    {
        parent::__construct();
    }
}
