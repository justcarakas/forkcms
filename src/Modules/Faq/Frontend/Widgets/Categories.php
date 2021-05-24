<?php

namespace ForkCMS\Modules\Faq\Frontend\Widgets;

use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Modules\Faq\Frontend\Helper\Model as FrontendFaqModel;

/**
 * This is a widget with faq categories
 */
class Categories extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();
        $this->parse();
    }

    private function parse(): void
    {
        $this->template->assign('widgetFaqCategories', FrontendFaqModel::getCategories());
    }
}
