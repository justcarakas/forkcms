<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Form\DeleteType;
use ForkCMS\Core\Domain\Header\Header;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
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
     */
    protected function handleForm(
        Request $request,
        string $formType,
        callable $defaultCallback = null,
        callable $validCallback = null,
        ?RedirectResponse $redirectResponse = null,
        object $formData = null,
        array $formOptions = [],
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
            return $validCallback($form);
        }

        return $defaultCallback($form);
    }

    protected function addDeleteForm(
        array $data,
        ActionSlug $deleteActionSlug,
        string $formType = DeleteType::class,
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
