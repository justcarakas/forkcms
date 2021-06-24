<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractActionController implements ActionControllerInterface
{
    private static ?ActionSlug $actionSlug = null;
    private string $templatePath;
    private string $pageTitle;
    private array $twigContext = [];

    public function __construct(private Environment $twig, private TranslatorInterface $translator)
    {
        $actionSlug = self::getActionSlug();
        $this->templatePath = sprintf(
            '@%s/Backend/Actions/%s.html.twig',
            $actionSlug->getModuleName(),
            $actionSlug->getActionName()
        );

        $this->pageTitle = $actionSlug->getModuleName()->asLabel();
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
        $actionSlug = self::getActionSlug();
        $this->twig->addGlobal('INTERFACE_LANGUAGE', $request->getLocale());
        $this->twig->addGlobal('SITE_TITLE', $_ENV['SITE_DEFAULT_TITLE']);
        $this->twig->addGlobal('page_title', $this->translator->trans($this->pageTitle));
        $this->twig->addGlobal('jsFiles', []);
        $this->twig->addGlobal('jsData', null);
        $this->twig->addGlobal('bodyID', Container::underscore($actionSlug->getModuleName()));
        $this->twig->addGlobal('bodyClass', str_replace('/', '_', $actionSlug->getSlug()));

        $this->execute($request);

        return $this->getResponse();
    }

    public function assign(string $key, mixed $value): void
    {
        $this->twigContext[$key] = $value;
    }

    abstract protected function execute(Request $request): void;

    public function getResponse(): Response
    {
        return new Response($this->twig->render($this->templatePath, $this->twigContext));
    }
}
