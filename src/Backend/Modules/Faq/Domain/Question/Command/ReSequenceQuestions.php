<?php

namespace Backend\Modules\Faq\Domain\Question\Command;

final class ReSequenceQuestions
{
    /**
     * @var int[]
     */
    private $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
