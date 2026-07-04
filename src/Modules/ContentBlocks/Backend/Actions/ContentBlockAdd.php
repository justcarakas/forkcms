<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command\CreateContentBlockRevision;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockType;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add a new content block.
 */
final class ContentBlockAdd extends AbstractFormActionController
{
    #[\Override]
    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleForm(
            request: $request,
            formType: ContentBlockType::class,
            formData: CreateContentBlockRevision::new(Locale::from($request->getLocale())),
            redirectResponse: new RedirectResponse(ContentBlockIndex::getActionSlug()->generateRoute($this->router)),
            successFlashMessageCallback: static function (FormInterface $form): FlashMessage {
                return FlashMessage::success('Added', ['%contentBlock%' => $form->getData()->title]);
            }
        );
    }
}
