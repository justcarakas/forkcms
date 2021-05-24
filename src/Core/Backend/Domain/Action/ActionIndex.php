<?php

namespace ForkCMS\Core\Backend\Domain\Action;

use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase;

/**
 * This class implements a lot of functionality that can be extended by the real action.
 * In this case this is the base class for the index action
 */
class ActionIndex extends Action
{
    /**
     * A datagrid instance
     *
     * @var DataGridDatabase
     */
    protected $dataGrid;
}
