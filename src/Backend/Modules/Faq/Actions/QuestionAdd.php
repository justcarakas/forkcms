<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\Category\CategoryRepository;
use Backend\Modules\Faq\Domain\Question\Command\CreateQuestion;
use Backend\Modules\Faq\Domain\Question\QuestionType;
use Backend\Core\Engine\Model as BackendModel;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\KernelInterface;

final class QuestionAdd extends ActionAdd
{
    /** @var CategoryRepository */
    private $categoryRepository;

    public function setKernel(KernelInterface $kernel = null): void
    {
        parent::setKernel($kernel);

        $this->categoryRepository = $this->get(CategoryRepository::class);
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
        $createQuestion = $form->getData();

        $this->get('command_bus')->handle($createQuestion);

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                ]
            )
        );
    }

    private function getForm(): Form
    {
        $category = null;
        if ($this->getRequest()->query->has('category')) {
            $category = $this->categoryRepository->find($this->getRequest()->query->getInt('category'));
        }

        $form = $this->createForm(QuestionType::class, new CreateQuestion($category));

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'QuestionIndex',
            null,
            null,
            $parameters
        );
    }
}
