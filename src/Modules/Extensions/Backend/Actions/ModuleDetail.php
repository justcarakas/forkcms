<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInformation;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\HttpFoundation\Request;

final class ModuleDetail extends AbstractActionController
{
    protected function execute(Request $request): void
    {
        $module = ModuleInformation::fromModule(ModuleName::fromString($request->attributes->get('slug')));
        $this->assign('module', $module);

        if (!array_key_exists($module->getModuleName(), $this->getRepository(Module::class)->findAllIndexed())) {
            $module->messages->addMessage(TranslationKey::message('InformationModuleIsNotInstalled'));
            $this->assign(
                'installForm',
                $this->formFactory->create(
                    ActionType::class,
                    [
                        'id' => $module->getModuleName(),
                    ],
                    [
                        'actionSlug' => ModuleInstall::getActionSlug(),
                    ]
                )->createView()
            );
        }
    }
}
