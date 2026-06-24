<?php

namespace ForkCMS\Modules\Frontend\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Frontend\Domain\ModuleSettings\ModuleSettingsType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModuleSettings extends AbstractFormActionController
{
    public function __construct(
        ActionServices $services,
        #[Autowire(env: 'SITE_DEFAULT_TITLE')]
        private readonly string $defaultTitle,
        #[Autowire(env: 'bool:SITE_DEFAULT_CONSENT_DIALOG_ENABLED')]
        private readonly bool $defaultConsentDialogEnabled,
    ) {
        parent::__construct($services);
    }

    protected function addBreadcrumbForRequest(Request $request): void
    {
        // no action specific breadcrumb needed
    }

    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleModuleSettingsForm(
            $request,
            ModuleSettingsType::class,
            [
                'site_title' => $this->defaultTitle,
                'consent_dialog_enabled' => $this->defaultConsentDialogEnabled,
            ],
        );
    }
}
