<?php

namespace ForkCMS\Modules\Blog\Tests\Frontend\Actions;

use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogCategories;
use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogPosts;
use ForkCMS\Core\Tests\Frontend\FrontendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class DetailTest extends FrontendWebTestCase
{
    public function testBlogPostHasDetailPage(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );

        self::assertPageLoadedCorrectly($client, '/en/blog', [LoadBlogPosts::BLOG_POST_TITLE]);
        self::assertClickOnLink($client, LoadBlogPosts::BLOG_POST_TITLE, [LoadBlogPosts::BLOG_POST_TITLE]);
        self::assertCurrentUrlEndsWith($client, '/en/blog/detail/' . LoadBlogPosts::BLOG_POST_SLUG);
    }

    public function testNonExistingBlogPostGives404(Client $client): void
    {
        self::assertHttpStatusCode404($client, '/en/blog/detail/non-existing');
    }
}
