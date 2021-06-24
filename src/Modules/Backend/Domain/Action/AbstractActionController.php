<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract class AbstractActionController implements ActionControllerInterface
{
    private static ?ActionSlug $actionSlug = null;
    private string $templatePath;

    public function __construct(private Environment $twig)
    {
        $actionSlug = self::getActionSlug();
        $this->templatePath = sprintf(
            '@%s/%s.html.twig',
            $actionSlug->getModuleName(),
            $actionSlug->getActionName()
        );
    }

    final public function changeTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }

    final public static function getActionSlug(): ActionSlug
    {
        return self::$actionSlug ?? self::$actionSlug = ActionSlug::fromFQCN(static::class);
    }

    public function __invoke(Request $request): Response
    {
        return new Response($this->twig->render($this->templatePath));
    }
}
