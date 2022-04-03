<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Core\Domain\Header\Header;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInformation;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstallerLocator;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository;
use Pageon\DoctrineDataGridBundle\Column\Column;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class ModuleIndex extends AbstractActionController
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
        private readonly ModuleInstallerLocator $moduleInstallerLocator,
        private readonly ModuleRepository $moduleRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
        parent::__construct(...func_get_args());
    }

    protected function execute(Request $request): void
    {
        $installedModules = $this->moduleRepository->findAllIndexed();

        $installed = [];
        $notInstalled = [];
        foreach ($this->moduleInstallerLocator->getModuleInstallersForOverview() as $installer) {
            if (array_key_exists($installer::getModuleName()->getName(), $installedModules)) {
                $installed[$installer::getModuleName()->getName()] = $installer->getInformation();
            } else {
                $notInstalled[$installer::getModuleName()->getName()] = $installer->getInformation();
            }
        }
        $this->assign(
            'installedModules',
            $this->dataGridFactory->forArray(ModuleInformation::class, $installed)
        );
        if ($this->authorizationChecker->isGranted(ModuleInstall::getActionSlug()->getModuleName()->asRole())) {
            $this->assign(
                'notInstalledModules',
                $this->dataGridFactory->forArray(
                    ModuleInformation::class,
                    $notInstalled,
                    null,
                    new Column(
                        name: 'moduleName',
                        label: 'lbl.Install',
                        valueCallback: [$this, 'getInstallButton'],
                        html: true,
                        showColumnLabel: false,
                    )
                ),
            );
        }
    }

    public function getInstallButton(string $moduleName): string
    {
        return $this->twig->render('@Extensions/Backend/Forms/ModuleInstall.html.twig', [
            'moduleName' => $moduleName,
            'installForm' => $this->formFactory->create(
                ActionType::class,
                [
                    'id' => $moduleName,
                ],
                [
                    'actionSlug' => ModuleInstall::getActionSlug(),
                ]
            )->createView(),
        ]);
    }
}
