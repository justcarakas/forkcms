<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use ForkCMS\Core\Domain\Header\Header;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractActionController implements ActionControllerInterface
{
    private string $templatePath;
    private string $pageTitle;
    /** @var array<string, mixed> */
    private array $twigContext = [];

    public function __construct(
        protected DataGridFactory $dataGridFactory,
        protected EntityManagerInterface $entityManager,
        protected Environment $twig,
        protected TranslatorInterface $translator,
        protected Header $header,
        protected RouterInterface $router,
        protected FormFactoryInterface $formFactory,
        protected MessageBusInterface $commandBus,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
        $actionSlug = self::getActionSlug();
        $this->templatePath = sprintf(
            '@%s/Backend/Actions/%s.html.twig',
            $actionSlug->getModuleName(),
            $actionSlug->getActionName()
        );

        $this->pageTitle = $this->buildPageTitle();
    }

    private function buildPageTitle(?string $prepend = null): string {
        $actionSlug = self::getActionSlug();
        return implode(
            ' - ',
            array_filter(
                [
                    $prepend,
                    $actionSlug->getActionName()->asLabel()->trans($this->translator),
                    $actionSlug->getModuleName()->asLabel()->trans($this->translator),
                ]
            )
        );
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

        return $this->getResponse($request);
    }

    final protected function assign(string $key, mixed $value): void
    {
        $this->twigContext[$key] = $value;
    }

    abstract protected function execute(Request $request): void;

    public function getResponse(Request $request): Response
    {
        return new Response($this->twig->render($this->templatePath, $this->twigContext));
    }

    public function getRepository(string $entityFQCN): EntityRepository
    {
        return $this->entityManager->getRepository($entityFQCN);
    }

    protected function getEntityFromRequest(Request $request, string $entityFQCN, string $key = 'slug'): object
    {
        return $this->getRepository($entityFQCN)->find($request->get($key) ?? $request->query->get($key));
    }

    protected function setBreadcrumbDetail(string $breadcrumbDetail): void
    {
        $this->assign('breadcrumbDetail', $breadcrumbDetail);
        $this->pageTitle = $this->buildPageTitle($breadcrumbDetail);
    }
}
