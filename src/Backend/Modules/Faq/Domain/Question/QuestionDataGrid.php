<?php

namespace Backend\Modules\Faq\Domain\Question;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\Category\Category;

class QuestionDataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale, Category $category)
    {
        parent::__construct(
            'SELECT DISTINCT q.id, qt.question, sequence
             FROM FaqQuestion q
             INNER JOIN FaqQuestionTranslation qt
                ON q.id = qt.questionId
                AND q.revisionId = qt.questionRevisionId
                AND qt.locale = :locale
                AND (q.status = :draft OR q.status = :active)
                AND q.category_id = :category',
            [
                'locale' => $locale,
                'draft' => Status::draft(),
                'active' => Status::active(),
                'category' => $category->getId(),
            ]
        );

        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(['data-module' => 'Faq', 'data-action' => 'QuestionReSequence']);

        if (BackendAuthentication::isAllowedAction('QuestionEdit')) {
            $editUrl = Model::createUrlForAction('QuestionEdit', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('question', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale, Category $category): string
    {
        return (new self($locale, $category))->getContent();
    }
}
