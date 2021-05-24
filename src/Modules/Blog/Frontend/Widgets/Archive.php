<?php

namespace ForkCMS\Modules\Blog\Frontend\Widgets;

use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Modules\Blog\Frontend\Helper\Model as FrontendBlogModel;

/**
 * This is a widget with the link to the archive
 */
class Archive extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    private function parse(): void
    {
        $this->template->assign('widgetBlogArchive', FrontendBlogModel::getArchiveNumbers());
    }
}
