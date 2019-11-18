<?php

namespace Backend\Modules\Faq\Domain\Question;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Model;
use Backend\Core\Engine\TemplateModifiers;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

class QuestionRevisionDataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale, Question $question)
    {
        parent::__construct(
            'SELECT DISTINCT q.id, q.revisionId, qt.question, q.status AS revisionStatus, q.createdOn
             FROM FaqQuestion q
             INNER JOIN FaqQuestionTranslation qt
                ON q.id = qt.questionId
                AND q.revisionId = qt.questionRevisionId
                AND qt.locale = :locale
                AND q.id = :question
                ORDER BY q.createdOn DESC',
            [
                'locale' => $locale,
                'question' => $question->getId(),
            ]
        );

        $this->setColumnHidden('revisionId');
        $this->setColumnFunction([TemplateModifiers::class, 'toLabel'], '[revisionStatus]', 'revisionStatus');
        $editUrl = Model::createUrlForAction('QuestionEdit', null, null, ['id' => '[id]', 'revisionId' => '[revisionId]'], false);
        $this->setColumnURL('question', $editUrl);
        $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
    }

    public static function getHtml(Locale $locale, Question $question): string
    {
        return (new self($locale, $question))->getContent();
    }
}
