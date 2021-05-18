<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Requirement\RequirementsChecker;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

final class RequirementCheckerController extends AbstractStepController
{
    private string $rootDir;

    public function __construct(
        Environment $twig,
        RouterInterface $router,
        RequirementsChecker $requirementsChecker,
        FormFactoryInterface $formFactory,
        string $rootDir
    ) {
        parent::__construct($twig, $router, $requirementsChecker, $formFactory);

        $this->rootDir = $rootDir;
    }

    public function __invoke(): Response
    {
        // if all our requirements are met, go to the next step
        if ($this->requirementsChecker->passes()) {
            return new RedirectResponse($this->router->generate('install_step2'));
        }

        return new Response(
            $this->twig->render(
                'step1.html.twig',
                [
                    'checker' => $this->requirementsChecker,
                    'rootDir' => realpath($this->rootDir),
                ]
            )
        );
    }
}
