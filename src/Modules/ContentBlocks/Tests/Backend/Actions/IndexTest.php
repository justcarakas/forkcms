<?php

namespace ForkCMS\Modules\ContentBlocks\Tests\Backend\Actions;

use ForkCMS\Core\Tests\Backend\BackendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexTest extends BackendWebTestCase
{
    public function testAuthenticationIsNeeded(Client $client): void
    {
        self::assertAuthenticationIsNeeded($client, '/private/en/content_blocks/index');
    }

    public function testIndexHasNoItems(Client $client): void
    {
        $this->login($client);

        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/content_blocks/index',
            [
                'There are no content blocks yet.',
                'Add content block',
            ]
        );
    }
}
