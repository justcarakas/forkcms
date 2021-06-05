<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStep;
use ForkCMS\Core\Installer\Domain\Installer\InstallForkCMS;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

final class InstallController
{
    public function __construct(
        private Environment $twig,
        private RouterInterface $router,
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $step = InstallerStep::install();
        $installerConfiguration = InstallerConfiguration::fromSession($request->getSession());

        if (!$installerConfiguration->isValidForStep($step)) {
            return new RedirectResponse($this->router->generate($step->previous()->route()));
        }

        $this->commandBus->dispatch(new InstallForkCMS($installerConfiguration));

        return new Response(
            $this->twig->render(
                $step->template(),
                [
                    'data' => $installerConfiguration,
                ]
            )
        );
    }
}
