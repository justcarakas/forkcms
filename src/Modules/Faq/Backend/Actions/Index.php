<?php

namespace ForkCMS\Modules\Faq\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridArray as BackendDataGridArray;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase as BackendDataGridDatabase;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Faq\Backend\Helper\Model as BackendFaqModel;

/**
 * This is the index-action (default), it will display the overview
 */
class Index extends BackendBaseActionIndex
{
    /**
     * The dataGrids
     *
     * @var array
     */
    private $dataGrids;

    /**
     * Default dataGird
     *
     * @var BackendDataGridArray
     */
    private $emptyDatagrid;

    public function execute(): void
    {
        parent::execute();
        $this->loadDatagrids();

        $this->parse();
        $this->display();
    }

    private function loadDatagrids(): void
    {
        // load all categories
        $categories = BackendFaqModel::getCategories(true);

        // loop categories and create a dataGrid for each one
        foreach ($categories as $categoryId => $categoryTitle) {
            $dataGrid = new BackendDataGridDatabase(
                BackendFaqModel::QUERY_DATAGRID_BROWSE,
                [BL::getWorkingLanguage(), $categoryId]
            );
            $dataGrid->setColumnFunction('htmlspecialchars', ['[question]'], 'question', false);
            $dataGrid->enableSequenceByDragAndDrop();
            $dataGrid->setColumnsHidden(['category_id', 'sequence']);
            $dataGrid->setColumnAttributes('question', ['class' => 'title']);
            $dataGrid->setRowAttributes(['id' => '[id]']);

            // check if this action is allowed
            if (BackendAuthentication::isAllowedAction('Edit')) {
                $dataGrid->setColumnURL('question', BackendModel::createUrlForAction('Edit') . '&amp;id=[id]');
                $dataGrid->addColumn(
                    'edit',
                    null,
                    BL::lbl('Edit'),
                    BackendModel::createUrlForAction('Edit') . '&amp;id=[id]',
                    BL::lbl('Edit')
                );
            }

            $dataGrid->setAttributes(
                [
                    'class' => 'table table-striped fork-data-grid jsDataGrid',
                    'data-sequence-drag-and-drop' => 'data-grid-faq',
                ]
            );

            // add dataGrid to list
            $this->dataGrids[] = [
                'id' => $categoryId,
                'title' => $categoryTitle,
                'content' => $dataGrid->getContent(),
            ];
        }

        // set empty datagrid
        $this->emptyDatagrid = new BackendDataGridArray(
            [[
                'dragAndDropHandle' => '',
                'sortHandle' => '',
                'question' => BL::msg('NoQuestionInCategory'),
                'edit' => '',
            ]]
        );
        $this->emptyDatagrid->setAttributes(
            [
                'class' => 'table table-striped fork-data-grid jsDataGrid emptyGrid',
                'data-sequence-drag-and-drop' => 'data-grid-faq',
            ]
        );
        $this->emptyDatagrid->setHeaderLabels(['edit' => null, 'dragAndDropHandle' => null, 'sortHandle' => ucfirst(BL::lbl('Move'))]);
    }

    protected function parse(): void
    {
        parent::parse();

        // parse dataGrids
        if (!empty($this->dataGrids)) {
            $this->template->assign('dataGrids', $this->dataGrids);
        }
        $this->template->assign('emptyDatagrid', $this->emptyDatagrid->getContent());
    }
}
