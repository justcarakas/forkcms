<?php

namespace ForkCMS\Modules\Backend\Controller;

use ForkCMS\Modules\Backend\Backend\Actions\NotFound;
use ForkCMS\Modules\Backend\Domain\Action\ActionControllerInterface;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\Navigation\Navigation;
use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class BackendController
{
    public function __construct(
        private ServiceLocator $actions,
        private Environment $twig,
        private Navigation $navigation,
    ) {
    }

    public function __invoke(
        Request $request,
        ActionSlug $actionSlug
    ): Response {
        try {
            $action = $this->actions->get($actionSlug->getFQCN());
        } catch (NotFoundExceptionInterface) {
            throw new InvalidArgumentException(sprintf('The action class %s must be registered as a service and implement %s', $actionSlug->getFQCN(), ActionControllerInterface::class));
        }

        $this->configureTwigForAction($request, $actionSlug);

        try {
            return $action($request);
        } catch (NotFoundHttpException) {
            return $this->actions->get(NotFound::class)($request);
        }
    }

    private function configureTwigForAction(Request $request, ActionSlug $actionSlug): void
    {
        $this->navigation->parse($this->twig);
        $this->twig->addGlobal('INTERFACE_LANGUAGE', $request->getLocale());
        $this->twig->addGlobal('SITE_TITLE', $_ENV['SITE_DEFAULT_TITLE']);
        $this->twig->addGlobal('jsFiles', []);
        $this->twig->addGlobal('jsData', '<script>var jsData = ' . json_encode([]) . '</script>');
        $this->twig->addGlobal('bodyID', Container::underscore($actionSlug->getModuleName()));
        $this->twig->addGlobal('bodyClass', str_replace('/', '_', $actionSlug->getSlug()));
        $this->twig->addGlobal('page_title', $actionSlug->getActionName());
    }
}
