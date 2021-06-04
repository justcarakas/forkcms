<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Database\DatabaseStepConfiguration;
use ForkCMS\Core\Installer\Domain\Database\DatabaseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DatabaseController extends AbstractStepController
{
    public function __invoke(Request $request): Response
    {
        return $this->handleInstallationStep(
            DatabaseType::class,
            DatabaseStepConfiguration::class,
            $request
        );
    }
}
