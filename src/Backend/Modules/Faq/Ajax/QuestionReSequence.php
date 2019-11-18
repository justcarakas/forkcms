<?php

namespace Backend\Modules\Faq\Ajax;

use Backend\Core\Ajax\UpdateSequence;
use Backend\Modules\Faq\Domain\Question\Command\ReSequenceQuestions;

final class QuestionReSequence extends UpdateSequence
{
    public function execute(): void
    {
        $this->setHandlerClass(ReSequenceQuestions::class);

        parent::execute();
    }
}
