<?php

namespace ForkCMS\Modules\Faq\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase as BackendDataGridDatabase;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Faq\Backend\Helper\Model as BackendFaqModel;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;

/**
 * This is the categories-action, it will display the overview of faq categories
 */
class Categories extends BackendBaseActionIndex
{
    /**
     * Deny the use of multiple categories
     *
     * @param bool
     */
    private $multipleCategoriesAllowed;

    public function execute(): void
    {
        parent::execute();

        $this->loadDataGrid();

        $this->parse();
        $this->display();
    }

    private function loadDataGrid(): void
    {
        // are multiple categories allowed?
        $this->multipleCategoriesAllowed = $this->get(ModuleSettingRepository::class)->get('Faq', 'allow_multiple_categories', true);

        // create dataGrid
        $this->dataGrid = new BackendDataGridDatabase(
            BackendFaqModel::QUERY_DATAGRID_BROWSE_CATEGORIES,
            [BL::getWorkingLanguage()]
        );
        $this->dataGrid->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);
        $this->dataGrid->setHeaderLabels(['num_items' => \SpoonFilter::ucfirst(BL::lbl('Amount'))]);
        if ($this->multipleCategoriesAllowed) {
            $this->dataGrid->enableSequenceByDragAndDrop();
        } else {
            $this->dataGrid->setColumnsHidden(['sequence']);
        }
        $this->dataGrid->setPaging(false);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('Index')) {
            $this->dataGrid->setColumnFunction(
                [__CLASS__, 'setClickableCount'],
                ['[num_items]', BackendModel::createUrlForAction('Index') . '&amp;category=[id]'],
                'num_items',
                true
            );
        }

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditCategory')) {
            $this->dataGrid->setColumnURL('title', BackendModel::createUrlForAction('EditCategory') . '&amp;id=[id]');
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

        $this->template->assign('dataGrid', (string) $this->dataGrid->getContent());

        // check if this action is allowed
        $this->template->assign('allowFaqAddCategory', $this->multipleCategoriesAllowed);
    }

    /**
     * Convert the count in a human readable one.
     *
     * @param int $count
     * @param string $link
     *
     * @return string
     */
    public static function setClickableCount(int $count, string $link): string
    {
        // return link in case of more than one item, one item, other
        if ($count > 1) {
            return '<a href="' . $link . '">' . $count . ' ' . BL::getLabel('Questions') . '</a>';
        }

        if ($count === 1) {
            return '<a href="' . $link . '">' . $count . ' ' . BL::getLabel('Question') . '</a>';
        }

        return '';
    }
}
