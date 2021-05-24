<?php

namespace ForkCMS\Modules\ContentBlocks\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Locale\Backend\Domain\Locale\Locale;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockDataGrid;

/**
 * This is the index-action (default), it will display the overview
 */
class Index extends BackendBaseActionIndex
{
    public function execute(): void
    {
        parent::execute();
        $this->template->assign('dataGrid', ContentBlockDataGrid::getHtml(Locale::workingLocale()));
        $this->parse();
        $this->display();
    }
}
