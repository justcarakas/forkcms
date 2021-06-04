<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;

final class InstallForkCMSHandler implements CommandHandlerInterface
{
    public function __invoke(InstallForkCMS $installForkCMS)
    {
    }
}
