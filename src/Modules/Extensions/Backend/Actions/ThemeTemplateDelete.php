<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractDeleteActionController;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\Command\DeleteThemeTemplate;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ThemeTemplateDelete extends AbstractDeleteActionController
{
    public function getFormResponse(Request $request): Response
    {
        $themeTemplate = $this->getRepository(ThemeTemplate::class)->find($request->request->all('action')['id']);

        return $this->handleDeleteForm(
            $request,
            DeleteThemeTemplate::class,
            ThemeTemplateIndex::getActionSlug()->withDefaultParameters(
                ['slug' => $themeTemplate->getTheme()->getName()]
            ),
            FlashMessage::success('DeletedTemplate', ['%1$s' => $themeTemplate->getName()]),
        );
    }
}
