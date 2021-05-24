<?php

namespace ForkCMS\Modules\Blog\Tests\Backend\Actions;

use ForkCMS\Core\Tests\Backend\BackendWebTestCase;
use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogCategories;
use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogPosts;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexTest extends BackendWebTestCase
{
    public function testAuthenticationIsNeeded(Client $client): void
    {
        self::assertAuthenticationIsNeeded($client, '/private/en/blog/index');
    }

    public function testIndexContainsBlogPosts(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );
        $this->login($client);

        self::assertPageLoadedCorrectly($client, '/private/en/blog/index', ['Blogpost for functional tests']);

        // some stuff we also want to see on the blog index
        self::assertResponseHasContent($client->getResponse(), 'Add article', 'Published articles');
    }
}
