<?php

namespace ForkCMS\Modules\Blog\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase as BackendDataGridDatabase;
use ForkCMS\Modules\Blog\Backend\Helper\Model as BackendBlogModel;

/**
 * This is the categories-action, it will display the overview of blog categories
 */
class Categories extends BackendBaseActionIndex
{
    public function execute(): void
    {
        parent::execute();
        $this->loadDataGrid();
        $this->parse();
        $this->display();
    }

    private function loadDataGrid(): void
    {
        // create datagrid
        $this->dataGrid = new BackendDataGridDatabase(
            BackendBlogModel::QUERY_DATAGRID_BROWSE_CATEGORIES,
            ['active', BL::getWorkingLanguage()]
        );

        $this->dataGrid->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);

        // set headers
        $this->dataGrid->setHeaderLabels([
            'num_items' => \SpoonFilter::ucfirst(BL::lbl('Amount')),
        ]);

        // sorting columns
        $this->dataGrid->setSortingColumns(['title', 'num_items'], 'title');

        // convert the count into a readable and clickable one
        $this->dataGrid->setColumnFunction(
            [__CLASS__, 'setClickableCount'],
            ['[num_items]', BackendModel::createUrlForAction('Index') . '&amp;category=[id]'],
            'num_items',
            true
        );

        // disable paging
        $this->dataGrid->setPaging(false);

        // add attributes, so the inline editing has all the needed data
        $this->dataGrid->setColumnAttributes('title', ['data-id' => '{id:[id]}']);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditCategory')) {
            // set column URLs
            $this->dataGrid->setColumnURL(
                'title',
                BackendModel::createUrlForAction('EditCategory') . '&amp;id=[id]'
            );

            // add column
            $this->dataGrid->addColumn(
                'edit',
                null,
                BL::lbl('Edit'),
                BackendModel::createUrlForAction('EditCategory') . '&amp;id=[id]',
                BL::lbl('Edit')
            );
        }
    }

    protected function parse(): void
    {
        parent::parse();

        $this->template->assign('dataGrid', $this->dataGrid->getContent());
    }

    /**
     * Convert the count in a human readable one.
     *
     * @param int $count The count.
     * @param string $link The link for the count.
     *
     * @return string
     */
    public static function setClickableCount(int $count, string $link): string
    {
        if ($count > 1) {
            return '<a href="' . $link . '">' . $count . ' ' . BL::getLabel('Articles') . '</a>';
        }

        if ($count === 1) {
            return '<a href="' . $link . '">' . $count . ' ' . BL::getLabel('Article') . '</a>';
        }

        return '';
    }
}
