<?php

namespace ForkCMS\Modules\Search\Tests\Frontend\Actions;

use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogPostComments;
use ForkCMS\Core\Tests\Frontend\FrontendWebTestCase;
use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogCategories;
use ForkCMS\Modules\Blog\Tests\DataFixtures\LoadBlogPosts;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexTest extends FrontendWebTestCase
{
    public function testSearchIndexWorks(Client $client): void
    {
        self::assertPageLoadedCorrectly(
            $client,
            '/en/search',
            [
                'Searchterm',
                'type="submit" name="submit" value="Search" />',
            ]
        );
    }

    public function testNotSubmittedSearchIndexDoesNotContainData(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
                LoadBlogPostComments::class,
            ]
        );

        self::assertHttpStatusCode200($client, '/en/search');
        self::assertResponseDoesNotHaveContent($client->getResponse(), LoadBlogPosts::BLOG_POST_TITLE);
    }

    public function testSubmittedSearchValidatesData(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
                LoadBlogPostComments::class,
            ]
        );

        self::assertHttpStatusCode200($client, '/en/search');

        $form = $this->getFormForSubmitButton($client, 'Search');

        // $_GET parameters should be set manually, since Fork uses them.
        $this->submitForm($client, $form, ['form' => 'search']);

        // result should not yet be found
        self::assertResponseHasContent($client->getResponse(), 'The searchterm is required.');
    }

    public function testSubmittedSearchIndexContainsData(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
                LoadBlogPostComments::class,
            ]
        );

        self::assertHttpStatusCode200($client, '/en/search');

        $form = $this->getFormForSubmitButton($client, 'Search');

        $this->submitForm(
            $client,
            $form,
            [
                'q' => substr(LoadBlogPosts::BLOG_POST_TITLE, 0, strpos(LoadBlogPosts::BLOG_POST_TITLE, ' ')),
                'submit' => 'Search',
                'form' => 'search',
            ]
        );

        self::assertResponseHasContent($client->getResponse(), LoadBlogPosts::BLOG_POST_TITLE);
    }
}
