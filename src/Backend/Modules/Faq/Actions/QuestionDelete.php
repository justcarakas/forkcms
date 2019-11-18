<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Faq\Domain\Question\Command\DeleteQuestion;
use Backend\Modules\Faq\Domain\Question\Question;
use Backend\Modules\Faq\Domain\Question\QuestionRepository;
use Backend\Modules\Faq\Domain\Question\Status;
use Symfony\Component\HttpKernel\KernelInterface;

final class QuestionDelete extends ActionDelete
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
        $question = $this->getQuestion();
        if (!$question instanceof Question) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));

            return;
        }

        $this->get('command_bus')->handle(new DeleteQuestion($question));

        $this->redirect($this->getBackLink(['report' => 'deleted', 'var' => $question]));
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
        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());

        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            return null;
        }

        return $this->questionRepository->findById($deleteForm->getData()['id'])[0] ?? null;
    }
}
