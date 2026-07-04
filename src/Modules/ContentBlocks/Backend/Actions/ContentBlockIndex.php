<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Backend\Actions;

use Doctrine\ORM\QueryBuilder;
use ForkCMS\Modules\Backend\Domain\Action\AbstractDataGridActionController;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use Pageon\DoctrineDataGridBundle\Column\Column;
use Symfony\Component\HttpFoundation\Request;

/**
 * Overview of the available content blocks.
 */
final class ContentBlockIndex extends AbstractDataGridActionController
{
    #[\Override]
    protected function execute(Request $request): void
    {
        $locale = $this->translator->getLocale();

        $this->renderDataGrid(
            Revision::class,
            static function (QueryBuilder $queryBuilder) use ($locale): void {
                $queryBuilder
                    ->andWhere('Revision.archivedOn IS NULL')
                    ->innerJoin('Revision.contentBlock', 'ContentBlock', 'WITH', 'ContentBlock.locale = :locale')
                    ->addSelect('ContentBlock')
                    ->setParameter('locale', $locale)
                    ->innerJoin('ContentBlock.widget', 'Widget')
                    ->addSelect('Widget')
                ;
            },
            null,
            'msg.NoContentBlocksFound',
            ...$this->getExtraColumns()
        );
    }

    /** @return Column[] */
    private function getExtraColumns(): array
    {
        $columns = [
            Column::createPropertyColumn(
                name: 'contentBlock',
                label: 'lbl.VisibleOnSite',
                entityAlias: null,
                sortable: false,
                filterable: false,
                order: 1,
                valueCallback: static fn (ContentBlock $contentBlock): bool => $contentBlock->isWidgetVisible(),
            ),
            Column::createPropertyColumn(
                name: 'createdBy',
                label: 'lbl.LastEditedBy',
                entityAlias: 'Revision',
                sortable: false,
                filterable: false,
                order: 2,
            ),
            Column::createPropertyColumn(
                name: 'createdOn',
                label: 'lbl.LastEditedOn',
                entityAlias: 'Revision',
                sortable: true,
                filterable: false,
                order: 3,
            ),
        ];

        if (!$this->isAllowed(ContentBlockEdit::getActionSlug())) {
            return $columns;
        }

        $columns[] = Column::createActionColumn(
            label: 'lbl.Edit',
            order: 5,
            route: 'backend_action',
            routeAttributesCallback: [Revision::class, 'dataGridEditLinkCallback'],
            class: 'btn btn-primary btn-sm',
            iconClass: 'fa fa-edit',
            columnAttributes: ['class' => 'fork-data-grid-action']
        );

        return $columns;
    }
}
