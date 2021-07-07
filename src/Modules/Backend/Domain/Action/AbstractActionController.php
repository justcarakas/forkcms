<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pfilsx\DataGrid\Grid\DataGridFactoryInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractActionController implements ActionControllerInterface
{
    private string $templatePath;
    private string $pageTitle;
    /** @var array<string, mixed> */
    private array $twigContext = [];

    public function __construct(
        protected DataGridFactoryInterface $dataGridFactory,
        protected EntityManagerInterface $entityManager,
        protected Environment $twig,
        protected TranslatorInterface $translator,
    ) {
        $actionSlug = self::getActionSlug();
        $this->templatePath = sprintf(
            '@%s/Backend/Actions/%s.html.twig',
            $actionSlug->getModuleName(),
            $actionSlug->getActionName()
        );

        $this->pageTitle = $actionSlug->getModuleName()->asLabel()->trans($this->translator);
    }

    final protected function changeTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }

    final public static function getActionSlug(): ActionSlug
    {
        return ActionSlug::fromFQCN(static::class);
    }

    public function __invoke(Request $request): Response
    {
        $this->execute($request);

        $this->twig->addGlobal('page_title', $this->pageTitle);

        return $this->getResponse();
    }

    final protected function assign(string $key, mixed $value): void
    {
        $this->twigContext[$key] = $value;
    }

    abstract protected function execute(Request $request): void;

    public function getResponse(): Response
    {
        return new Response($this->twig->render($this->templatePath, $this->twigContext));
    }
}
