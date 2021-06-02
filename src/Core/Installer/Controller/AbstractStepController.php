<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use ForkCMS\Core\Installer\Domain\Installer\InstallerConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStep;
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
        InstallerStep $step,
        string $formTypeClass,
        string $dataClass,
        Request $request
    ): Response {
        if ($this->requirementsChecker->hasErrors()) {
            return new RedirectResponse($this->router->generate(InstallerStep::requirements()->route()));
        }

        $form = $this->formFactory->create(
            $formTypeClass,
            $this->getFormData($dataClass, InstallationData::fromSession($request->getSession()))
        );
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

    private function getFormData(string $dataClass, InstallationData $fromSession): InstallerConfiguration
    {
        return $dataClass($fromSession);
    }
}
