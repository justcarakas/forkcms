<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

use Backend\Modules\Faq\Domain\Question\Question;
use Backend\Modules\Faq\Domain\Question\QuestionRepository;

final class UpdateQuestionHandler
{
    /** @var QuestionRepository */
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(UpdateQuestion $updateQuestion): void
    {
        $updateQuestion->revisionId = $this->questionRepository->getNextRevisionId($updateQuestion->getId());

        $this->questionRepository->add(Question::fromDataTransferObject($updateQuestion));
    }
}
