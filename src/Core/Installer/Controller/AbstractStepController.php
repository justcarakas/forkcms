<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use ForkCMS\Core\Installer\Domain\Installer\InstallerHandler;
use ForkCMS\Core\Installer\Domain\Requirement\RequirementsChecker;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

abstract class AbstractStepController
{
    protected Environment $twig;
    protected RouterInterface $router;
    protected RequirementsChecker $requirementsChecker;
    protected FormFactoryInterface $formFactory;

    public function __construct(
        Environment $twig,
        RouterInterface $router,
        RequirementsChecker $requirementsChecker,
        FormFactoryInterface $formFactory
    ) {
        $this->twig = $twig;
        $this->router = $router;
        $this->requirementsChecker = $requirementsChecker;
        $this->formFactory = $formFactory;
    }

    abstract public function __invoke(Request $request): Response;

    final protected function handleInstallationStep(
        int $step,
        string $formTypeClass,
        InstallerHandler $handler,
        Request $request
    ): Response {
        if ($this->requirementsChecker->hasErrors()) {
            return new RedirectResponse($this->router->generate('install_step1'));
        }

        $form = $this->formFactory->create($formTypeClass, $this->getInstallationData($request));
        if ($handler->process($form, $request)) {
            return new RedirectResponse($this->router->generate('install_step' . ($step + 1)));
        }

        return new Response(
            $this->twig->render(
                'step' . $step . '.html.twig',
                [
                    'form' => $form->createView(),
                ]
            )
        );
    }

    final protected function getInstallationData(Request $request): InstallationData
    {
        if (!$request->getSession()->has('installation_data')) {
            $request->getSession()->set('installation_data', new InstallationData());
        }

        return $request->getSession()->get('installation_data');
    }
}
