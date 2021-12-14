<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Header\Header;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Backend\Domain\Dashboard\Widget;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
        private ServiceLocator $backendDashboardWidgets,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
        parent::__construct($dataGridFactory, $entityManager, $twig, $translator, $header, $router);
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
                $moduleWidget->getWidget()->asLabel(),
                $this->backendDashboardWidgets->get($fullyQualifiedClassName)($request)
            );
        }
        $this->assign('widgets', $widgets);
    }
}
