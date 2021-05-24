<?php

namespace ForkCMS\Modules\Blog\Frontend\Actions;

use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;
use ForkCMS\Modules\Locale\Frontend\Domain\Translator\Language as FL;
use ForkCMS\Core\Frontend\Helper\Rss as FrontendRSS;
use ForkCMS\Core\Frontend\Helper\RssItem as FrontendRSSItem;
use ForkCMS\Modules\Blog\Frontend\Helper\Model as FrontendBlogModel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This is the RSS-feed for comments on a certain article.
 */
class ArticleCommentsRss extends FrontendBaseBlock
{
    public function execute(): void
    {
        parent::execute();

        $this->generateRss();
    }

    private function generateRss()
    {
        $blogPost = $this->getBlogPost();
        $blogPostComments = FrontendBlogModel::getComments($blogPost['id']);
        $rss = new FrontendRSS(
            vsprintf(FL::msg('CommentsOn'), [$blogPost['title']]),
            $this->getRssFeedLink($blogPost),
            ''
        );
        $blogPostUrl = $this->getBlogPostLink($blogPost);

        foreach ($blogPostComments as $blogPostComment) {
            $rss->addItem($this->getRssFeedItemForBlogPostComment($blogPostComment, $blogPost['title'], $blogPostUrl));
        }

        $rss->parse();
    }

    private function getRssFeedLink(array $blogPost): string
    {
        return SITE_URL
               . FrontendNavigation::getUrlForBlock($this->getModule(), $this->getAction())
               . '/' . $blogPost['url'];
    }

    private function getBlogPostLink(array $blogPost): string
    {
        return SITE_URL
               . FrontendNavigation::getUrlForBlock($this->getModule(), 'Detail')
               . '/' . $blogPost['url'];
    }

    private function getRssFeedItemForBlogPostComment(
        array $blogPostComment,
        string $blogPostTitle,
        string $blogPostUrl
    ): FrontendRSSItem {
        $rssItem = new FrontendRSSItem(
            $blogPostComment['author'] . ' ' . FL::lbl('On') . ' ' . $blogPostTitle,
            $blogPostUrl . '/#comment-' . $blogPostComment['id'],
            $blogPostComment['text']
        );

        $rssItem->setPublicationDate($blogPostComment['created_on']);
        $rssItem->setAuthor(empty($blogPostComment['email']) ? $blogPostComment['author'] : $blogPostComment['email']);

        return $rssItem;
    }

    private function getBlogPost(): array
    {
        if ($this->url->getParameter(1) === null) {
            throw new NotFoundHttpException();
        }

        $blogPost = FrontendBlogModel::get($this->url->getParameter(1));

        if (empty($blogPost)) {
            throw new NotFoundHttpException();
        }

        return $blogPost;
    }
}
