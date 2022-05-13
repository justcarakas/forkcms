<?php

namespace ForkCMS\Modules\Pages\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Pages\Domain\ModuleSettings\ModuleSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModuleSettings extends AbstractFormActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleModuleSettingsForm(
            $request,
            ModuleSettingsType::class,
            $this->getModuleName(),
            ['site_title' => $_ENV['SITE_DEFAULT_TITLE']],
        );
    }
}
