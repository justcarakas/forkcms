<?php

namespace ForkCMS\Modules\Pages\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\Action;
use ForkCMS\Modules\Locale\Backend\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Command\CreatePage;
use ForkCMS\Modules\Pages\Domain\Page\Form\PageType;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\Page\PageRepository;
use ForkCMS\Modules\Pages\Domain\Page\Status;
use ForkCMS\Modules\Pages\Backend\Helper\Model as BackendPagesModel;
use ForkCMS\Core\Common\ModulesSettings;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\KernelInterface;

final class PageAdd extends Action
{
    /** @var ModulesSettings */
    private $settings;

    /** @var PageRepository */
    private $pageRepository;

    /** @var MessageBus */
    private $commandBus;

    public function setKernel(KernelInterface $kernel = null): void
    {
        parent::setKernel($kernel);

        $this->settings = $this->getContainer()->get(ModulesSettings::class);
        $this->pageRepository = $this->getContainer()->get(PageRepository::class);
        $this->commandBus = $this->getContainer()->get('command_bus');
    }

    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $this->handleForm($form);
    }

    private function handleForm(Form $form): void
    {
        $createPage = $form->getData();

        $this->commandBus->handle($createPage);

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createPage->title,
                ]
            )
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            PageType::class,
            new CreatePage(
                Locale::workingLocale(),
                $this->settings->get($this->getModule(), 'default_template', 1),
                $this->getParent(),
                $this->getCopyFromPage()
            )
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'PageIndex',
            null,
            null,
            $parameters
        );
    }

    protected function parse(): void
    {
        $this->template->assign('tree', BackendPagesModel::getTreeHTML());
    }

    private function getParent(): ?Page
    {
        $parentPage = $this->pageRepository->findOneBy(
            [
                'id' => $this->getRequest()->query->getInt('parent'),
                'status' => Status::active(),
                'locale' => Locale::workingLocale(),
            ]
        );

        if ($parentPage instanceof Page && $parentPage->isAllowChildren()) {
            return $parentPage;
        }

        return null;
    }

    private function getCopyFromPage(): ?Page
    {
        $parentPage = $this->pageRepository->findOneBy(
            [
                'id' => $this->getRequest()->query->getInt('copy'),
                'status' => Status::active(),
                'locale' => Locale::workingLocale(),
            ]
        );

        if ($parentPage instanceof Page) {
            return $parentPage;
        }

        return null;
    }
}
