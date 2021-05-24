<?php

namespace ForkCMS\Modules\Blog\Frontend\Widgets;

use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Modules\Blog\Frontend\Helper\Model as FrontendBlogModel;

/**
 * This is a widget with recent comments on all blog-articles
 */
class RecentComments extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    private function parse(): void
    {
        $this->template->assign('widgetBlogRecentComments', FrontendBlogModel::getRecentComments(5));
    }
}
