<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStepConfiguration;
use ForkCMS\Core\Installer\Domain\Requirement\RequirementsChecker;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

abstract class AbstractStepController
{
    public function __construct(
        protected Environment $twig,
        protected RouterInterface $router,
        protected RequirementsChecker $requirementsChecker,
        protected FormFactoryInterface $formFactory,
        protected MessageBusInterface $commandBus
    ) {
    }

    abstract public function __invoke(Request $request): Response;

    final protected function handleInstallationStep(
        string $formTypeClass,
        string $dataClass,
        Request $request
    ): Response {
        $installerConfiguration = InstallerConfiguration::fromSession($request->getSession());
        $installerStepConfiguration = $this->getFormData($dataClass, $installerConfiguration);
        /** @var $dataClass InstallerStepConfiguration */
        $step = $dataClass::getStep();
        if (!$installerConfiguration->isValidForStep($step)) {
            return new RedirectResponse($this->router->generate($step->previous()->route()));
        }

        $form = $this->formFactory->create($formTypeClass, $installerStepConfiguration);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch($form->getData());

            return new RedirectResponse($this->router->generate($step->next()->route()));
        }

        return new Response(
            $this->twig->render(
                $step->template(),
                [
                    'form' => $form->createView(),
                ]
            )
        );
    }

    private function getFormData(
        string $dataClass,
        InstallerConfiguration $installerConfiguration
    ): InstallerStepConfiguration {
        /** @var $dataClass InstallerStepConfiguration */
        return $dataClass::fromInstallerConfiguration($installerConfiguration);
    }
}
