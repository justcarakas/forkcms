<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Core\Domain\Header\Header;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractFormActionController extends AbstractActionController
{
    public function __construct(
        DataGridFactory $dataGridFactory,
        EntityManagerInterface $entityManager,
        Environment $twig,
        TranslatorInterface $translator,
        Header $header,
        RouterInterface $router,
        protected FormFactoryInterface $formFactory,
        protected MessageBusInterface $commandBus,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($dataGridFactory, $entityManager, $twig, $translator, $header, $router);
    }

    protected function execute(Request $request): void
    {
    }

    public function getResponse(Request $request): Response
    {
        return $this->getFormResponse($request) ?? parent::getResponse($request);
    }

    abstract protected function getFormResponse(Request $request): ?Response;

    /**
     * @param null|callable(FormInterface): Response|FormInterface|null $defaultCallback
     * @param null|callable(FormInterface): Response|FormInterface|null $validCallback
     * @param null|callable(FormInterface): FlashMessage $flashMessageCallback
     * @param null|callable(FormInterface): Event $eventCallback
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
        ?callable $eventCallback = null,
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
            $response =  $validCallback($form);
            if ($flashMessage instanceof FlashMessage) {
                $this->header->addFlashMessage($flashMessage);
            } elseif (is_callable($flashMessageCallback)) {
                $this->header->addFlashMessage($flashMessageCallback($form));
            }
            if (is_callable($eventCallback)) {
                $this->eventDispatcher->dispatch($eventCallback($form));
            }

            return $response;
        }

        return $defaultCallback($form);
    }

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
