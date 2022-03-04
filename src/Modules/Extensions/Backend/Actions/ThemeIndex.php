<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Extensions\Domain\Theme\InstallableTheme;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Extensions\Domain\Theme\ThemeRepository;
use Symfony\Component\HttpFoundation\Request;

final class ThemeIndex extends AbstractActionController
{
    protected function execute(Request $request): void
    {
        /** @var ThemeRepository $themeRepository */
        $themeRepository = $this->getRepository(Theme::class);
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
                $themeRepository->findInstallable()
            )
        );
    }
}
