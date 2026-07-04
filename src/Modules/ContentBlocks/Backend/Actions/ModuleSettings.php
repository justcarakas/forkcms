<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\ContentBlocks\Domain\ModuleSettings\ModuleSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModuleSettings extends AbstractFormActionController
{
    #[\Override]
    protected function addBreadcrumbForRequest(Request $request): void
    {
        // no action specific breadcrumb needed
    }

    #[\Override]
    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleModuleSettingsForm(
            $request,
            ModuleSettingsType::class,
        );
    }
}
