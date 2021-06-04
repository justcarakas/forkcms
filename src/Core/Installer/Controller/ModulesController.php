<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Module\ModulesStepConfiguration;
use ForkCMS\Core\Installer\Domain\Module\ModulesType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModulesController extends AbstractStepController
{
    public function __invoke(Request $request): Response
    {
        return $this->handleInstallationStep(
            ModulesType::class,
            ModulesStepConfiguration::class,
            $request
        );
    }
}
