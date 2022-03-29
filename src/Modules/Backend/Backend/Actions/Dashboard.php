<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Header\Header;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Backend\Domain\Dashboard\Widget;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class Dashboard extends AbstractActionController
{
    public function __construct(
        DataGridFactory $dataGridFactory,
        EntityManagerInterface $entityManager,
        Environment $twig,
        TranslatorInterface $translator,
        Header $header,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        MessageBusInterface $commandBus,
        SerializerInterface $serializer,
        private readonly ServiceLocator $backendDashboardWidgets,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
        parent::__construct(...func_get_args());
    }

    protected function execute(Request $request): void
    {
        $widgets = [];
        foreach ($this->backendDashboardWidgets->getProvidedServices() as $fullyQualifiedClassName) {
            $moduleWidget = ModuleWidget::fromFQCN($fullyQualifiedClassName);
            if (!$this->authorizationChecker->isGranted($moduleWidget->asRole())) {
                continue;
            }

            $widgets[] = new Widget(
                $moduleWidget->getModule()->asLabel(),
                $moduleWidget->getWidget()->asLabel(),
                $this->backendDashboardWidgets->get($fullyQualifiedClassName)($request)
            );
        }
        $this->assign('widgets', $widgets);
    }
}
