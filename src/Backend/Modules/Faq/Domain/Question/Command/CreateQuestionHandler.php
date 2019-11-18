<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

use Backend\Modules\Faq\Domain\Question\Question;
use Backend\Modules\Faq\Domain\Question\QuestionRepository;

final class CreateQuestionHandler
{
    /** @var QuestionRepository */
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(CreateQuestion $createQuestion): void
    {
        $createQuestion->setId($this->questionRepository->getNextId());
        $createQuestion->sequence = $this->questionRepository->getNextSequence();

        $this->questionRepository->add(
            Question::fromDataTransferObject($createQuestion)
        );
    }
}
