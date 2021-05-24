<?php

namespace ForkCMS\Modules\Authentication\Backend\Tests\Actions;

use ForkCMS\Core\Tests\Backend\BackendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class LogoutTest extends BackendWebTestCase
{
    public function testLogoutActionRedirectsYouToLoginAfterLoggingOut(Client $client): void
    {
        $this->login($client);

        $client->request('GET', '/private/en/authentication/logout');
        $client->followRedirect();

        self::assertStringContainsString(
            '/private/en/authentication/index',
            $client->getHistory()->current()->getUri()
        );
    }
}
