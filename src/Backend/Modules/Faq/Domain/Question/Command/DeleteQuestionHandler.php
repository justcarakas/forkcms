<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

use Backend\Modules\Faq\Domain\Question\QuestionRepository;

final class DeleteQuestionHandler
{
    /** @var QuestionRepository */
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(DeleteQuestion $deleteQuestion): void
    {
        $this->questionRepository->remove($deleteQuestion->getQuestion());
    }
}
