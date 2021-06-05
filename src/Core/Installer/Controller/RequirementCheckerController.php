<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Configuration\ConfigurationParser;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStep;
use ForkCMS\Core\Installer\Domain\Requirement\RequirementsChecker;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

final class RequirementCheckerController extends AbstractStepController
{
    public function __construct(
        Environment $twig,
        RouterInterface $router,
        RequirementsChecker $requirementsChecker,
        FormFactoryInterface $formFactory,
        MessageBusInterface $commandBus,
        private ConfigurationParser $configurationParser,
        private string $rootDir
    ) {
        parent::__construct($twig, $router, $requirementsChecker, $formFactory, $commandBus);
    }

    public function __invoke(Request $request): Response
    {
        $step = InstallerStep::requirements();

        // if all our requirements are met, go to the next step
        if ($this->requirementsChecker->passes()) {
            $installerConfiguration = InstallerConfiguration::fromSession($request->getSession());
            $installerConfiguration->withRequirementsStep();
            $this->configurationParser->loadFromFile($installerConfiguration);

            return new RedirectResponse($this->router->generate($step->next()->route()));
        }

        return new Response(
            $this->twig->render(
                $step->template(),
                [
                    'checker' => $this->requirementsChecker,
                    'rootDir' => realpath($this->rootDir),
                ]
            )
        );
    }
}
