<?php

namespace ForkCMS\Modules\Blog\Frontend\Actions;

use ForkCMS\Core\Common\ModulesSettings;
use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Locale\Frontend\Domain\Translator\Language;
use ForkCMS\Modules\Blog\Frontend\Helper\Model as FrontendBlogModel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Index extends FrontendBaseBlock
{
    /** @var array */
    private $articles;

    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->getData();
        $this->parse();
    }

    private function buildPaginationConfig(): array
    {
        $requestedPage = $this->url->getParameter('page', 'int', 1);
        $numberOfItems = FrontendBlogModel::getAllCount();

        $limit = $this->get(ModulesSettings::class)->get($this->getModule(), 'overview_num_items', 10);
        $numberOfPages = (int) ceil($numberOfItems / $limit);

        if ($numberOfPages === 0) {
            $numberOfPages = 1;
        }

        // Check if the page exists
        if ($requestedPage > $numberOfPages || $requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        return [
            'url' => FrontendNavigation::getUrlForBlock($this->getModule()),
            'limit' => $limit,
            'offset' => ($requestedPage * $limit) - $limit,
            'requested_page' => $requestedPage,
            'num_items' => $numberOfItems,
            'num_pages' => $numberOfPages,
        ];
    }

    private function getData(): void
    {
        $this->pagination = $this->buildPaginationConfig();
        $this->articles = FrontendBlogModel::getAll($this->pagination['limit'], $this->pagination['offset']);
    }

    private function addLinksToRssFeeds(): void
    {
        // General rss feed
        $this->header->addRssLink(
            $this->get(ModulesSettings::class)->get($this->getModule(), 'rss_title_' . LANGUAGE, SITE_DEFAULT_TITLE),
            FrontendNavigation::getUrlForBlock($this->getModule(), 'Rss')
        );

        // Rss feed for the comments of this blog
        $this->header->addRssLink(
            Language::lbl('RecentComments'),
            FrontendNavigation::getUrlForBlock($this->getModule(), 'CommentsRss')
        );
    }

    private function parse(): void
    {
        $this->addLinksToRssFeeds();
        $this->parsePagination();

        $this->template->assign('items', $this->articles);
    }
}
