<?php

namespace ForkCMS\Modules\Internationalisation\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\Command\ChangeInternationalisationModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\Command\ChangeModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\InstalledLocalesType;
use ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\ModuleSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModuleSettings extends AbstractFormActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleSettingsForm(
            $request,
            ModuleSettingsType::class,
            new ChangeModuleSettings(...$this->getRepository(InstalledLocale::class)->findAll()),
        );
    }
}
