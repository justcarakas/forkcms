<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Extensions\Domain\Theme\InstallableTheme;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Extensions\Domain\Theme\ThemeRepository;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\HttpFoundation\Request;

final class ThemeDetail extends AbstractActionController
{
    protected function execute(Request $request): void
    {
        /** @var ThemeRepository $themeRepository */
        $themeRepository = $this->getRepository(Theme::class);
        $name = $request->attributes->get('slug');
        $theme = $themeRepository->find($name)
            ?? $themeRepository->findInstallable()[$name]
            ?? InstallableTheme::fromMessage(TranslationKey::error('InformationFileIsMissing'));

        if ($theme instanceof Theme) {
            $theme = InstallableTheme::fromTheme($theme);
        }

        $this->assign('theme', $theme);
        $this->assign(
            'themeInstallForm',
            $this->formFactory->create(
                ActionType::class,
                [
                    'name' => $theme->name,
                ],
                [
                    'id_field_name' => 'name',
                    'actionSlug' => ThemeInstall::getActionSlug()
                ]
            )->createView()
        );
        $this->setBreadcrumbDetail($theme->name);
    }
}
