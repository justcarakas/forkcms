<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Faq\Domain\Question\Question;
use Backend\Modules\Faq\Domain\Question\Command\UpdateQuestion;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Faq\Domain\Question\QuestionRepository;
use Backend\Modules\Faq\Domain\Question\QuestionRevisionDataGrid;
use Backend\Modules\Faq\Domain\Question\QuestionType;
use Backend\Modules\Faq\Domain\Question\Status;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\KernelInterface;

final class QuestionEdit extends ActionEdit
{
    /** @var QuestionRepository */
    private $questionRepository;

    public function setKernel(KernelInterface $kernel = null): void
    {
        parent::setKernel($kernel);

        $this->questionRepository = $this->get(QuestionRepository::class);
    }

    public function execute(): void
    {
        parent::execute();

        $question = $this->getQuestion();

        if (!$question instanceof Question) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }

        $this->loadDeleteForm($question);

        $form = $this->getForm($question);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('question', $question);
            $this->template->assign(
                'revisionDataGrid',
                QuestionRevisionDataGrid::getHtml(Locale::workingLocale(), $question)
            );

            $this->parse();
            $this->display();

            return;
        }

        $this->handleForm($form);
    }

    private function loadDeleteForm(Question $question): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $question->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'QuestionDelete',
            ]
        );

        $this->template->assign('deleteForm', $deleteForm->createView());
    }

    private function getForm(Question $question): Form
    {
        $form = $this->createForm(QuestionType::class, new UpdateQuestion($question));

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function handleForm(Form $form): void
    {
        $updateQuestion = $form->getData();
        $updateQuestion->status = $form->get('saveAsDraft')->isClicked() ? Status::draft() : Status::active();
        $this->get('command_bus')->handle($updateQuestion);

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                ]
            )
        );
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

    private function getQuestion(): ?Question
    {
        $id = $this->getRequest()->query->getInt('id');
        $revisionId = $this->getRequest()->query->getInt('revisionId');
        if ($revisionId !== 0) {
            return $this->questionRepository->findOneBy(['id' => $id, 'revisionId' => $revisionId]);
        }

        $question = $this->questionRepository->findOneBy(['id' => $id, 'status' => Status::active()]);
        if ($question instanceof Question) {
            return $question;
        }

        return $this->questionRepository->findOneBy(['id' => $id, 'status' => Status::draft()]);
    }
}
