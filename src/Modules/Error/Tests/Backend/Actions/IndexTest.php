<?php

namespace ForkCMS\Modules\Error\Tests\Backend\Actions;

use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language;
use ForkCMS\Core\Tests\Backend\BackendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class IndexTest extends BackendWebTestCase
{
    public function testAuthenticationIsNotNeeded(Client $client): void
    {
        $this->logout($client);

        self::assertHttpStatusCode($client, '/private/en/error/index', Response::HTTP_BAD_REQUEST);
        self::assertCurrentUrlEndsWith($client, '/private/en/error/index');
    }

    public function testModuleNotAllowed(Client $client): void
    {
        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/error/index?type=module-not-allowed',
            [
                'You have insufficient rights for this module.',
            ],
            Response::HTTP_FORBIDDEN
        );
    }

    public function testActionNotAllowed(Client $client): void
    {
        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/error/index?type=action-not-allowed',
            [
                'You have insufficient rights for this action.',
            ],
            Response::HTTP_FORBIDDEN
        );
    }

    public function testNotFound(Client $client): void
    {
        Language::setLocale('en');
        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/error/index?type=not-found',
            [
                Language::err('NotFound', 'Error'),
            ],
            Response::HTTP_NOT_FOUND
        );
    }
}
