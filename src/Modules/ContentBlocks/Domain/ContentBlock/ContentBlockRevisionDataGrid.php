<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridFunctions;
use ForkCMS\Core\Backend\Helper\Model;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language;
use ForkCMS\Modules\Locale\Backend\Domain\Locale\Locale;
use SpoonFilter;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class ContentBlockRevisionDataGrid extends DataGridDatabase
{
    public function __construct(ContentBlock $contentBlock, Locale $locale)
    {
        parent::__construct(
            'SELECT i.id, i.revision_id, i.title, UNIX_TIMESTAMP(i.edited_on) AS edited_on, i.user_id
             FROM content_blocks AS i
             WHERE i.status = :archived AND i.id = :id AND i.language = :language
             ORDER BY i.edited_on DESC',
            ['archived' => Status::archived(), 'language' => $locale, 'id' => $contentBlock->getId()]
        );

        // hide columns
        $this->setColumnsHidden(['id', 'revision_id']);

        // disable paging
        $this->setPaging(false);

        // set headers
        $this->setHeaderLabels(
            [
                'user_id' => SpoonFilter::ucfirst(Language::lbl('By')),
                'edited_on' => SpoonFilter::ucfirst(Language::lbl('LastEditedOn')),
            ]
        );

        // set column-functions
        $this->setColumnFunction([DataGridFunctions::class, 'getUser'], ['[user_id]'], 'user_id');
        $this->setColumnFunction([DataGridFunctions::class, 'getTimeAgo'], ['[edited_on]'], 'edited_on');
        $this->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);

        // check if this action is allowed
        if (Authentication::isAllowedAction('Edit')) {
            $editRevisionUrl = Model::createUrlForAction(
                'Edit',
                null,
                null,
                ['id' => '[id]', 'revision' => '[revision_id]'],
                false
            );
            // set column URLs
            $this->setColumnURL('title', $editRevisionUrl);

            // add use column
            $this->addColumn(
                'use_revision',
                null,
                Language::lbl('UseThisVersion'),
                $editRevisionUrl,
                Language::lbl('UseThisVersion')
            );
        }
    }

    public static function getHtml(ContentBlock $contentBlock, Locale $locale): string
    {
        return (new self($contentBlock, $locale))->getContent();
    }
}
