<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

use Backend\Modules\Faq\Domain\Question\Question;
use Backend\Modules\Faq\Domain\Question\QuestionRepository;

final class ReSequenceQuestionsHandler
{
    /**
     * @var QuestionRepository
     */
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(ReSequenceQuestions $reSequenceQuestions): void
    {
        $sequenceById = array_flip($reSequenceQuestions->getIds());
        $questions = $this->questionRepository->findBy(['id' => $reSequenceQuestions->getIds()]);

        /** @var Question $question */
        foreach ($questions as $question) {
            $question->changeSequence($sequenceById[$question->getId()]);
        }
    }
}
