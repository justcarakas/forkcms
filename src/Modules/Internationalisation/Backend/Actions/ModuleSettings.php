<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\Command\ChangeModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\ModuleSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ModuleSettings extends AbstractFormActionController
{
    public function __construct(ActionServices $services, private SluggerInterface $slugger)
    {
        parent::__construct($services);
    }

    #[\Override]
    protected function addBreadcrumbForRequest(Request $request): void
    {
        // no action specific breadcrumb needed
    }

    #[\Override]
    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleSettingsForm(
            $request,
            ModuleSettingsType::class,
            new ChangeModuleSettings($this->slugger, ...$this->getRepository(InstalledLocale::class)->findAll()),
        );
    }
}
