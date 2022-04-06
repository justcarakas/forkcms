<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\Command\ChangeThemeTemplate;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Edit a theme template positions and default blocks
 */
final class ThemeTemplateEdit extends AbstractFormActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        $themeTemplate = $this->getEntityFromRequest($request, ThemeTemplate::class);
        $this->assign('theme', $themeTemplate->getTheme());

        $this->setBreadcrumbDetail(
            sprintf(
                '%1$s: %2$s',
                $themeTemplate->getTheme()->getName(),
                $themeTemplate->getName()
            )
        );

        if (!$themeTemplate->isDefault()) {
            $this->addDeleteForm(['id' => $themeTemplate->getId()], ThemeTemplateDelete::getActionSlug());
        }

        $changeThemeTemplate = new ChangeThemeTemplate($themeTemplate);

        return $this->handleForm(
            request: $request,
            formType: ThemeTemplateType::class,
            formData: $changeThemeTemplate,
            redirectResponse: new RedirectResponse(
                ThemeTemplateIndex::getActionSlug()->generateRoute(
                    $this->router,
                    ['slug' => $changeThemeTemplate->theme->getName()]
                )
            ),
            formOptions: ['show_overwrite' => true, 'show_status' => !$themeTemplate->isDefault()],
            flashMessageCallback: static function (FormInterface $form): FlashMessage {
                return FlashMessage::success('EditedTemplate', ['%1$s' => $form->getData()->name]);
            }
        );
    }
}
