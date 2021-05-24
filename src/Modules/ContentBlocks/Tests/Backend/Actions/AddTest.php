<?php

namespace ForkCMS\Modules\ContentBlocks\Tests\Backend\Actions;

use ForkCMS\Core\Tests\Backend\BackendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class AddTest extends BackendWebTestCase
{
    public function testAuthenticationIsNeeded(Client $client): void
    {
        self::assertAuthenticationIsNeeded($client, '/private/en/content_blocks/index');
    }

    public function testFormIsDisplayed(Client $client): void
    {
        $this->login($client);

        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/content_blocks/add',
            [
                'Title<abbr data-bs-toggle="tooltip" aria-label="Required field" title="Required field">*</abbr>',
                'Visible on site',
                'Add content block',
            ]
        );
    }
}
