<?php

namespace ForkCMS\Modules\Tags\Tests\Frontend\Actions;

use ForkCMS\Modules\Tags\Tests\DataFixtures\LoadTagsModulesTags;
use ForkCMS\Modules\Tags\Tests\DataFixtures\LoadTagsTags;
use ForkCMS\Core\Tests\Frontend\FrontendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexTest extends FrontendWebTestCase
{
    public function testTagsIndexShowsTags(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadTagsTags::class,
                LoadTagsModulesTags::class,
            ]
        );

        self::assertPageLoadedCorrectly(
            $client,
            '/en/tags',
            [
                '<a href="/en/tags/detail/most-used" rel="tag">',
                LoadTagsTags::TAGS_TAG_2_NAME,
                '<span class="badge badge-primary">6</span>',
            ]
        );
    }
}
