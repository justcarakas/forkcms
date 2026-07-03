<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\Action;

use Pageon\DoctrineDataGridBundle\Column\Column;

abstract class AbstractDataGridActionController extends AbstractActionController
{
    public function renderDataGrid(
        string $entityFullyQualifiedClassName,
        ?callable $queryBuilderCallback = null,
        ?int $limit = null,
        ?string $noResultsMessage = null,
        Column ...$extraColumns
    ): void {
        $this->assign(
            'backend_data_grid',
            $this->dataGridFactory->forEntity(
                $entityFullyQualifiedClassName,
                $queryBuilderCallback,
                $limit,
                $noResultsMessage,
                ...
                $extraColumns
            )
        );
    }
}
