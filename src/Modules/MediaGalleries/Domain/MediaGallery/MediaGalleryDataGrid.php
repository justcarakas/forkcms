<?php

namespace ForkCMS\Modules\MediaGalleries\Domain\MediaGallery;

use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridFunctions;
use ForkCMS\Core\Backend\Helper\Model;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language;
use SpoonFilter;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class MediaGalleryDataGrid extends DataGridDatabase
{
    public function __construct()
    {
        parent::__construct(
            'SELECT i.id, i.title, i.action, UNIX_TIMESTAMP(i.editedOn) AS editedOn
             FROM MediaGallery AS i'
        );

        $this->setHeaderLabels(['title' => SpoonFilter::ucfirst(Language::lbl('Title'))]);
        $this->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);
        $this->setSortingFunctions();
        $this->setExtraFunctions();
    }

    public static function getHtml(): string
    {
        return (string) (new self())->getContent();
    }

    private function setSortingFunctions(): void
    {
        $this->setSortingColumns(
            [
                'title',
                'action',
                'editedOn',
            ],
            'title'
        );
        $this->setSortParameter('asc');
    }

    private function setExtraFunctions(): void
    {
        $this->setColumnFunction(
            [new DataGridFunctions(), 'getLongDate'],
            ['[editedOn]'],
            'editedOn',
            true
        );

        if (Authentication::isAllowedAction('MediaGalleryEdit')) {
            // Define edit url
            $editUrl = Model::createUrlForAction('MediaGalleryEdit', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('MediaGalleryEdit'));
        }
    }
}
