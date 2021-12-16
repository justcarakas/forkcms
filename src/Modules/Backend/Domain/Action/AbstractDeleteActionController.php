<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use ForkCMS\Core\Domain\Form\DeleteType;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractDeleteActionController extends AbstractFormActionController
{
    protected function handleDeleteForm(Request $request, string $deleteCommandFullyQualifiedClassName, ActionSlug $redirectActionSlug): RedirectResponse
    {
        $response = $this->handleForm(
            request: $request,
            formType: DeleteType::class,
            flashMessage: FlashMessage::success('Deleted'),
            formOptions: ['actionSlug' => self::getActionSlug()],
            defaultCallback: function () use ($redirectActionSlug): RedirectResponse {
                $this->header->addFlashMessage(FlashMessage::error('NotFound'));

                return new RedirectResponse($redirectActionSlug->generateRoute($this->router));
            },
            validCallback: function (FormInterface $form) use ($deleteCommandFullyQualifiedClassName, $redirectActionSlug): RedirectResponse {
                $this->commandBus->dispatch(new $deleteCommandFullyQualifiedClassName($form->getData()['id']));

                return new RedirectResponse($redirectActionSlug->generateRoute($this->router));
            }
        );

        if ($response instanceof RedirectResponse) {
           return $response;
        }

        throw new RuntimeException('Invalid response');
    }
}
