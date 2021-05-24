<?php

namespace ForkCMS\Modules\Blog\Tests\Frontend\Actions;

use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogCategories;
use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogPosts;
use ForkCMS\Core\Tests\Frontend\FrontendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class CategoryTest extends FrontendWebTestCase
{
    public function testCategoryHasPage(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );

        self::assertPageLoadedCorrectly($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, [LoadBlogCategories::BLOG_CATEGORY_TITLE]);
    }

    public function testNonExistingCategoryPostGives404(Client $client): void
    {
        self::assertHttpStatusCode404($client, '/en/blog/category/non-existing');
    }

    public function testCategoryPageContainsBlogPost(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );

        self::assertPageLoadedCorrectly($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, [LoadBlogCategories::BLOG_CATEGORY_TITLE]);
        self::assertClickOnLink($client, LoadBlogPosts::BLOG_POST_TITLE, [LoadBlogPosts::BLOG_POST_TITLE]);
        self::assertCurrentUrlEndsWith($client, '/en/blog/detail/' . LoadBlogPosts::BLOG_POST_SLUG);
    }

    public function testNonExistingPageGives404(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );

        self::assertPageLoadedCorrectly($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, [LoadBlogCategories::BLOG_CATEGORY_TITLE]);
        self::assertHttpStatusCode404($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, 'GET', ['page' => 34]);
    }
}
