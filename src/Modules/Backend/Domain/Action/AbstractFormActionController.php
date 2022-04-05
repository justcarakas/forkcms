<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractFormActionController extends AbstractActionController
{
    protected function execute(Request $request): void
    {
    }

    public function getResponse(Request $request): Response
    {
        return $this->getFormResponse($request) ?? parent::getResponse($request);
    }

    abstract protected function getFormResponse(Request $request): ?Response;

    /**
     * @param array<string, mixed> $formOptions
     * @param null|callable(FormInterface):Response|callable(FormInterface):FormInterface|callable(FormInterface):null $defaultCallback
     * @param null|callable(FormInterface):Response|callable(FormInterface):FormInterface|callable(FormInterface):null $validCallback
     * @param null|callable(FormInterface):FlashMessage $flashMessageCallback
     */
    protected function handleForm(
        Request $request,
        string $formType,
        object $formData = null,
        FlashMessage $flashMessage = null,
        ?RedirectResponse $redirectResponse = null,
        array $formOptions = [],
        ?callable $defaultCallback = null,
        ?callable $validCallback = null,
        ?callable $flashMessageCallback = null,
    ): Response|FormInterface|null {
        $defaultCallback ??= function (FormInterface $form): ?FormInterface {
            $this->assign('backend_form', $form->createView());

            return null;
        };
        $validCallback ??= function (FormInterface $form) use ($redirectResponse): ?Response {
            $this->commandBus->dispatch($form->getData());

            return $redirectResponse;
        };

        $form = $this->formFactory->create($formType, $formData, $formOptions);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $validCallback($form);
            if ($flashMessage instanceof FlashMessage) {
                $this->header->addFlashMessage($flashMessage);
            } elseif (is_callable($flashMessageCallback)) {
                $this->header->addFlashMessage($flashMessageCallback($form));
            }

            return $response;
        }

        return $defaultCallback($form);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    protected function addDeleteForm(
        array $data,
        ActionSlug $deleteActionSlug,
        string $formType = ActionType::class,
        array $options = []
    ): void {
        $this->assign('crudDeleteAction', $deleteActionSlug->getActionName());
        $this->assign(
            'backend_delete_form',
            $this->formFactory->create(
                $formType,
                $data,
                array_merge(['actionSlug' => $deleteActionSlug], $options)
            )->createView()
        );
    }
}
