<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

use Backend\Modules\Faq\Domain\Question\QuestionDataTransferObject;
use Backend\Modules\Faq\Domain\Question\Question;

final class UpdateQuestion extends QuestionDataTransferObject
{
    public function __construct(Question $question)
    {
        // make sure we have an existing question
        parent::__construct($question);
    }
}
