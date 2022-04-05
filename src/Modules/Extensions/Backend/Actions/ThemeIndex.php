<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessageType;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Extensions\Domain\Theme\Command\ActivateTheme;
use ForkCMS\Modules\Extensions\Domain\Theme\InstallableTheme;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Extensions\Domain\Theme\ThemeRepository;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\HttpFoundation\Request;

final class ThemeIndex extends AbstractActionController
{
    public function __construct(ActionServices $services, private readonly ThemeRepository $themeRepository)
    {
        parent::__construct($services);
    }

    protected function execute(Request $request): void
    {
        $this->assign(
            'installableThemes',
            array_map(
                function (InstallableTheme $theme): array {
                    return [
                        'installForm' => $this->formFactory->create(
                            ActionType::class,
                            [
                                'name' => $theme->name,
                            ],
                            [
                                'id_field_name' => 'name',
                                'actionSlug' => ThemeInstall::getActionSlug()
                            ]
                        )->createView(),
                        'theme' => $theme,
                    ];
                },
                $this->themeRepository->findInstallable()
            )
        );
        $this->assign(
            'installedThemes',
            array_map(
                function (Theme $theme): array {
                    return [
                        'activateForm' => $this->formFactory->create(
                            ActionType::class,
                            $theme,
                            [
                                'id_field_name' => 'name',
                                'actionSlug' => ThemeActivate::getActionSlug()
                            ]
                        )->createView(),
                        'theme' => InstallableTheme::fromTheme($theme),
                    ];
                },
                $this->themeRepository->findAll()
            )
        );
    }
}
