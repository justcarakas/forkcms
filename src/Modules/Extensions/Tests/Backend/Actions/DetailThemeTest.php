<?php

namespace ForkCMS\Modules\Extensions\Tests\Backend\Actions;

use ForkCMS\Core\Tests\Backend\BackendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class DetailThemeTest extends BackendWebTestCase
{
    public function testAuthenticationIsNeeded(Client $client): void
    {
        self::assertAuthenticationIsNeeded($client, '/private/en/extensions/detail_theme?theme=Fork');
    }

    public function testIndexHasModules(Client $client): void
    {
        $this->login($client);

        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/extensions/detail_theme?theme=Fork',
            [
                'Core/Layout/Templates/Home.html.twig',
                'class="positions">top, main',
                'Version',
                'Description',
            ]
        );
    }
}
