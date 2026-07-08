<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\tests;

use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Core\tests\WebTestCase;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\DataCollectorTranslator;
use Throwable;

abstract class BackendWebTestCase extends WebTestCase
{
    public function testAuthenticationIsNeeded(): void
    {
        if (defined(static::class . '::TEST_URL') === true) {
            self::loadPage(loginBackendUser: false);
            self::assertAuthenticationIsNeeded(static::TEST_URL);
        }
    }

    public function testTranslations(): void
    {
        if (defined(static::class . '::TEST_URL') === true) {
            self::loadPage(enableProfiler: true);

            $dataCollector = self::getContainer()->get('translator.data_collector');
            $missingTranslations = array_filter($dataCollector->getCollectedMessages(), static fn (array $message): bool => $message['state'] === DataCollectorTranslator::MESSAGE_MISSING);
            self::assertSame([], $missingTranslations, 'Missing translations found.');
        }
    }


    /** @return ($loginBackendUser is true ? User : null) */
    final protected static function loadPage(?string $url = null, bool $enableProfiler = false, bool $loginBackendUser = true): ?User
    {
        $user = null;
        if ($loginBackendUser) {
            $user = self::loginBackendUser(url: $url);
        }

        if (defined(static::class . '::TEST_URL') === true) {
            $url = $url ?? static::TEST_URL;

            if ($enableProfiler && $url !== null) {
                $url .= str_contains($url, '?') ? '&enable-framework-profiler=1' : '?enable-framework-profiler=1';
            }
        }

        if ($url === null) {
            static::fail('No URL defined.');
        }

        self::request(Request::METHOD_GET, $url);

        return $user;
    }

    final protected static function loginBackendUser(string $email = 'test@example.com', ?string $url = null): User
    {
        try {
            $userRespository = static::getContainer()->get(UserRepository::class);
        } catch (Throwable) {
            static::fail('User repository not found.');
        }

        $user = $userRespository->findOneBy(['email' => $email]);
        static::assertNotNull($user, 'User with email "' . $email . '" not found.');
        Ensure::isInstanceOf(static::getClient(), KernelBrowser::class)->loginUser($user, 'backend');
        if (defined(static::class . '::TEST_URL') === true || $url !== null) {
            static::request(Request::METHOD_GET, $url ?? static::TEST_URL);
        }

        return $user;
    }

    /**
     * The data grid filter is deliberately not a real <form> (it can be embedded inside a bigger
     * form, e.g. a tab on an edit page, and HTML doesn't support nested forms) - see the
     * core--data-grid-filter Stimulus controller
     * (src/Core/assets/js/controllers/data_grid_filter_controller.js),
     * which reloads the enclosing <turbo-frame> instead. This mimics that: read the same target
     * straight off the controller's data attributes and navigate there directly.
     */
    final protected static function filterDataGrid(string $filter, string $value): void
    {
        $container = static::getCrawler()
            ->filter('#content .fork-data-grid [data-controller="core--data-grid-filter"]')
            ->reduce(static fn (Crawler $node): bool => $node->filter(
                '[data-core--data-grid-filter-target="field"][value="' . $filter . '"], option[value="' . $filter . '"]'
            )->count() > 0);
        self::assertGreaterThan(0, $container->count(), 'Filter ' . $filter . ' not found in data grid with value ' . $value . '.');

        $action = $container->attr('data-core--data-grid-filter-action-value');
        self::assertNotNull($action);
        $filterFieldName = $container->attr('data-core--data-grid-filter-field-name-value');
        $filterValueName = $container->attr('data-core--data-grid-filter-value-name-value');

        $separator = str_contains($action, '?') ? '&' : '?';
        static::request(
            Request::METHOD_GET,
            $action . $separator . http_build_query([$filterFieldName => $filter, $filterValueName => $value])
        );
    }

    final protected static function assertAuthenticationIsNeeded(
        string $url,
        string $method = Request::METHOD_GET
    ): void {
        static::assertRedirect($url, '/private/en/backend/authentication-login', $method);
    }

    final protected static function assertDataGridHasLink(string $text, ?string $url = null): void
    {
        $crawler = static::getCrawler()
            ->filter('#content .fork-data-grid table')
            ->selectLink($text);

        self::assertMinCount(1, $crawler, 'Link "' . $text . '" not found in data grid.');

        if ($url !== null) {
            self::assertSame($url, $crawler->attr('href'), 'Link "' . $text . '" has wrong URL.');
        }
    }

    final protected static function assertDataGridNotHasLink(string $text): void
    {
        $crawler = static::getCrawler()
            ->filter('#content .fork-data-grid table')
            ->selectLink($text);

        self::assertCount(0, $crawler, 'Found link "' . $text . '" in data grid, but it should not be there.');
    }

    final protected static function assertDataGridIsEmpty(): void
    {
        static::assertCount(
            1,
            static::getCrawler()->filter('#content .fork-data-grid + .empty-state'),
            'Data grid is not empty.'
        );
    }

    final protected static function assertDataGridNotEmpty(): void
    {
        static::assertCount(
            0,
            static::getCrawler()->filter('#content .fork-data-grid + .empty-state'),
            'Data grid is empty'
        );
    }

    protected static function assertEmptyFormSubmission(string $formName, int $expectedErrorCount, string $submitButtonLabel): void
    {
        self::submitForm($submitButtonLabel, []);
        self::assertMinCount(
            $expectedErrorCount,
            self::getCrawler()->filter('#content form[name="' . $formName . '"] .form-control.is-invalid'),
            'Not all required fields are marked as invalid.'
        );
        if ($expectedErrorCount === 0) {
            self::getClient()?->followRedirect();
        }
    }
}
