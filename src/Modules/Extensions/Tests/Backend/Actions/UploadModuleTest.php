<?php

namespace ForkCMS\Modules\Extensions\Tests\Backend\Actions;

use ForkCMS\Core\Tests\Backend\BackendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class UploadModuleTest extends BackendWebTestCase
{
    public function testAuthenticationIsNeeded(Client $client): void
    {
        self::assertAuthenticationIsNeeded($client, '/private/en/extensions/upload_module');
    }

    public function testUploadPage(Client $client): void
    {
        $this->login($client);

        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/extensions/upload_module',
            [
                'Install',
                '<label for="file" class="form-label">',
            ]
        );
    }
}
