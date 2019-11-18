<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

use Backend\Modules\Faq\Domain\Question\Question;

final class DeleteQuestion
{
    /** @var Question */
    private $question;

    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }
}
