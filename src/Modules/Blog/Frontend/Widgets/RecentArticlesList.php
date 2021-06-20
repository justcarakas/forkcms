<?php

namespace ForkCMS\Modules\Blog\Frontend\Widgets;

use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Blog\Frontend\Helper\Model as FrontendBlogModel;

/**
 * This is a widget with recent blog-articles
 */
class RecentArticlesList extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    private function parse(): void
    {
        // get RSS-link
        $rssTitle = $this->get(ModuleSettingRepository::class)->get('Blog', 'rss_title_' . LANGUAGE, SITE_DEFAULT_TITLE);
        $rssLink = FrontendNavigation::getUrlForBlock('Blog', 'Rss');

        // add RSS-feed into the metaCustom
        $this->header->addRssLink($rssTitle, $rssLink);

        // assign comments
        $this->template->assign(
            'widgetBlogRecentArticlesList',
            FrontendBlogModel::getAll($this->get(ModuleSettingRepository::class)->get('Blog', 'recent_articles_list_num_items', 5))
        );
        $this->template->assign('widgetBlogRecentArticlesFullRssLink', $rssLink);
    }
}
