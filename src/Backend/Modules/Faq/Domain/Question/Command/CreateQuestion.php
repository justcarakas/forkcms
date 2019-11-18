<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

use Backend\Modules\Faq\Domain\Category\Category;
use Backend\Modules\Faq\Domain\Question\QuestionDataTransferObject;

final class CreateQuestion extends QuestionDataTransferObject
{
    public function __construct(Category $category = null)
    {
        parent::__construct();

        $this->category = $category;
    }
}
