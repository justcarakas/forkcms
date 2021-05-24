<?php

namespace ForkCMS\Core\Backend\Domain\DataGrid;

use SpoonDatagridSourceArray;

/**
 * A datagrid with an array as source
 */
class DataGridArray extends DataGrid
{
    public function __construct(array $data)
    {
        $source = new SpoonDatagridSourceArray($data);
        parent::__construct($source);
    }
}
