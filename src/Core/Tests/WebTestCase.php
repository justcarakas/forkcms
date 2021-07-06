<?php

namespace ForkCMS\Core\Tests;

use ForkCMS\Core\Domain\Kernel\Kernel;
use ForkCMS\Core\Domain\PDO\ForkConnection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

/**
 * WebTestCase is the base class for functional tests.
 */
abstract class WebTestCase extends BaseWebTestCase
{
    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;
    protected const TEST_ENVIRONMENT = 'test';

    protected function getEnvironment(): string
    {
        return 'test';
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Inject the kernelBrowser in the data
        $client = static::createClient(['environment' => static::TEST_ENVIRONMENT]);
        $data = $this->getProvidedData();
        $data[] = $client;
        $this->__construct($this->getName(), $data, $this->dataName());
        $this->resetDataBase($client);
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function resetDataBase(KernelBrowser $client): void
    {
        $this->emptyTestDatabase();
        $rootDir = $client->getContainer()->getParameter('kernel.project_dir');
        $this->executeSql(
            file_get_contents($rootDir . '/tests/data/test_db.sql')
        );
    }

    protected function emptyTestDatabase(): void
    {
        $connection = ForkConnection::get();
        foreach ($connection->getTables() as $table) {
            $connection->exec(
                'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS ' . $table . '; SET FOREIGN_KEY_CHECKS = 1;'
            );
        }
    }

    protected function executeSql(string $sql): void
    {
        ForkConnection::get()->exec(trim($sql));
    }

    protected static function assertIs404(KernelBrowser $client): void
    {
        self::assertEquals(
            Response::HTTP_NOT_FOUND,
            $client->getResponse()->getStatusCode()
        );
    }

    protected static function assertIs200(KernelBrowser $client): void
    {
        self::assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );
    }

    /**
     * Submits the form and mimics the GET parameters, since they aren't added
     * by default in the functional tests
     */
    protected function submitForm(KernelBrowser $client, Form $form, array $data = [], bool $setValues = true): void
    {
        $values = $data;
        // @TODO remove this once SpoonForm has been removed
        if (!$setValues) {
            // Get parameters should be set manually. Symfony uses the request object,
            // but spoon still checks the $_GET and $_POST parameters
            foreach ($data as $key => $value) {
                $_GET[$key] = $value;
                $_POST[$key] = $value;
            }

            $values = [];
        }

        $client->submit($form, $values);

        // @TODO remove this once SpoonForm has been removed
        if (!$setValues) {
            foreach ($data as $key => $value) {
                unset($_GET[$key], $_POST[$key]);
            }
        }
    }

    /**
     * Edits the data of a form
     */
    protected function submitEditForm(KernelBrowser $client, Form $form, array $data = []): void
    {
        $originalData = [];
        foreach ($form->all() as $fieldName => $formField) {
            $originalData[$fieldName] = $formField->getValue();
        }

        $data = array_merge($originalData, $data);

        $this->submitForm($client, $form, $data);
    }

    /**
     * Do a request with the given GET parameters
     */
    protected function requestWithGetParameters(
        KernelBrowser $client,
        string $url,
        array $data = []
    ): Crawler {
        $this->setGetParameters($data);
        $request = $client->request('GET', $url, $data);
        $this->unsetGetParameters($data);

        return $request;
    }

    /**
     * Set the GET parameters, as some of the old code relies on GET
     */
    protected function setGetParameters(array $data = []): void
    {
        foreach ($data as $key => $value) {
            $_GET[$key] = $value;
        }
    }

    /**
     * Unset the GET parameters, as some of the old code relies on GET
     */
    protected function unsetGetParameters(array $data = []): void
    {
        if (empty($data)) {
            $_GET = [];

            return;
        }

        foreach ($data as $key => $value) {
            unset($_GET[$key]);
        }
    }

    protected static function assertCurrentUrlEndsWith(KernelBrowser $client, string $partialUrl): void
    {
        self::assertStringEndsWith($partialUrl, $client->getHistory()->current()->getUri());
    }

    protected static function assertGetsRedirected(
        KernelBrowser $client,
        string $initialUrl,
        string $expectedUrl,
        string $requestMethod = 'GET',
        array $requestParameters = [],
        int $maxRedirects = null,
        int $expectedHttpResponseCode = Response::HTTP_OK
    ): void {
        $maxRedirects !== null ? $client->setMaxRedirects($maxRedirects) : $client->followRedirects();

        $client->request($requestMethod, $initialUrl, $requestParameters);

        $response = $client->getResponse();
        self::assertNotNull($response, 'No response received');

        self::assertCurrentUrlContains($client, $expectedUrl);
        self::assertEquals($expectedHttpResponseCode, $response->getStatusCode(), $response->getContent());
    }

    protected static function assertPageLoadedCorrectly(
        KernelBrowser $client,
        string $url,
        array $expectedContent,
        int $httpStatusCode = Response::HTTP_OK,
        string $requestMethod = 'GET',
        array $requestParameters = []
    ): void {
        self::assertHttpStatusCode($client, $url, $httpStatusCode, $requestMethod, $requestParameters);
        $response = $client->getResponse();

        self::assertNotNull($response, 'No response received');
        self::assertResponseHasContent($response, ...$expectedContent);
    }

    protected static function assertClickOnLink(
        KernelBrowser $client,
        string $linkText,
        array $expectedContent,
        int $httpStatusCode = Response::HTTP_OK,
        string $requestMethod = 'GET',
        array $requestParameters = []
    ): void {
        self::assertPageLoadedCorrectly(
            $client,
            $client->getCrawler()->selectLink($linkText)->link()->getUri(),
            $expectedContent,
            $httpStatusCode,
            $requestMethod,
            $requestParameters
        );
    }

    protected static function assertResponseHasContent(Response $response, string ...$content): void
    {
        foreach ($content as $expectedContent) {
            self::assertStringContainsString($expectedContent, $response->getContent());
        }
    }

    protected static function assertResponseDoesNotHaveContent(Response $response, string ...$content): void
    {
        foreach ($content as $notExpectedContent) {
            self::assertStringNotContainsStringIgnoringCase($notExpectedContent, $response->getContent());
        }
    }

    protected static function assertCurrentUrlContains(KernelBrowser $client, string ...$partialUrls): void
    {
        foreach ($partialUrls as $partialUrl) {
            self::assertStringContainsString($partialUrl, $client->getHistory()->current()->getUri());
        }
    }

    protected static function assertHttpStatusCode(
        KernelBrowser $client,
        string $url,
        int $httpStatusCode,
        string $requestMethod = 'GET',
        array $requestParameters = []
    ): void {
        $client->request($requestMethod, $url, $requestParameters);
        $response = $client->getResponse();
        self::assertNotNull($response, 'No response received');
        self::assertEquals($httpStatusCode, $response->getStatusCode());
    }

    protected static function assertHttpStatusCode200(
        KernelBrowser $client,
        string $url,
        string $requestMethod = 'GET',
        array $requestParameters = []
    ): void {
        self::assertHttpStatusCode(
            $client,
            $url,
            Response::HTTP_OK,
            $requestMethod,
            $requestParameters
        );
    }

    protected static function assertHttpStatusCode404(
        KernelBrowser $client,
        string $url,
        string $requestMethod = 'GET',
        array $requestParameters = []
    ): void {
        self::assertHttpStatusCode(
            $client,
            $url,
            Response::HTTP_NOT_FOUND,
            $requestMethod,
            $requestParameters
        );
    }

    protected function getFormForSubmitButton(
        KernelBrowser $client,
        string $buttonText,
        string $filterSelector = null
    ): Form {
        $crawler = $client->getCrawler();

        if ($filterSelector !== null) {
            $crawler = $crawler->filter($filterSelector);
        }

        return $crawler->selectButton($buttonText)->form();
    }
}
