<?php

namespace ForkCMS\Modules\Search\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\Action;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase as BackendDataGridDatabase;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridFunctions as BackendDataGridFunctions;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Modules\Search\Backend\Helper\Model as BackendSearchModel;

/**
 * This is the statistics-action, it will display the overview of search statistics
 */
class Statistics extends Action
{
    public function execute(): void
    {
        parent::execute();
        $this->showDataGrid();
        $this->display();
    }

    private function showDataGrid(): void
    {
        $dataGrid = new BackendDataGridDatabase(
            BackendSearchModel::QUERY_DATAGRID_BROWSE_STATISTICS,
            [BL::getWorkingLanguage()]
        );
        $dataGrid->setColumnsHidden(['data']);
        $dataGrid->addColumn('referrer', BL::lbl('Referrer'));
        $dataGrid->setHeaderLabels(['time' => \SpoonFilter::ucfirst(BL::lbl('SearchedOn'))]);

        // set column function
        $dataGrid->setColumnFunction([__CLASS__, 'parseRefererInDataGrid'], '[data]', 'referrer');
        $dataGrid->setColumnFunction(
            [new BackendDataGridFunctions(), 'getLongDate'],
            ['[time]'],
            'time',
            true
        );
        $dataGrid->setColumnFunction('htmlspecialchars', ['[term]'], 'term', false);

        $dataGrid->setSortingColumns(['time', 'term'], 'time');
        $dataGrid->setSortParameter('desc');

        $this->template->assign('dataGrid', $dataGrid->getContent());
    }

    public static function parseRefererInDataGrid(string $data): string
    {
        $data = unserialize($data, ['allowed_classes' => false]);
        if (!isset($data['server']['HTTP_REFERER'])) {
            return '';
        }

        $referrer = htmlspecialchars($data['server']['HTTP_REFERER']);

        return '<a href="' . $referrer . '">' . $referrer . '</a>';
    }
}
