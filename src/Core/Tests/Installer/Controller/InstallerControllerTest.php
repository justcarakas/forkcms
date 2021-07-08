<?php

namespace ForkCMS\Core\Tests\Installer\Controller;

use ForkCMS\Core\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @group installer
 */
class InstallerControllerTest extends WebTestCase
{
    protected const TEST_ENVIRONMENT = 'test_install';

    /** @var string */
    private $rootDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rootDir = $this->getProvidedData()[0]->getContainer()->getParameter('kernel.project_dir');
    }

    protected function onNotSuccessfulTest(Throwable $throwable): void
    {
        // put back our env.local file
        if ($this->rootDir !== null) {
            $this->putLocalEnvFileBack();
        }

        parent::onNotSuccessfulTest($throwable);
    }

    public function testNoStepActionAction(KernelBrowser $client): void
    {
        $client->request('GET', '/');
        $client->followRedirect();

        // we should be redirected to the first step
        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertCurrentUrlEndsWith($client, '/install/1');
    }

    public function testInstallationProcess(KernelBrowser $client): void
    {
        // make sure we have a clean slate and our parameters file is backed up
        $this->emptyTestDatabase();
        $this->backupLocalEnvFile();

        self::assertGetsRedirected($client, '/', '/install/2');
        self::assertGetsRedirected($client, '/install', '/install/2');
        $this->runTroughStep2($client);
        $this->runTroughStep3($client);
        $this->runTroughStep4($client);
        $this->runTroughStep5($client);

        // put back our parameters file
        $this->putLocalEnvFileBack();
    }

    private function runTroughStep2(KernelBrowser $client): void
    {
        self::assertCurrentUrlEndsWith($client, '/install/2');

        $form = $this->getFormForSubmitButton($client, 'Next');
        $form['install_locales[locales][0]']->tick();
        $form['install_locales[locales][1]']->tick();
        $form['install_locales[locales][2]']->tick();
        $this->submitForm(
            $client,
            $form,
            [
                'install_locales[multilingual]' => '1',
                'install_locales[defaultLocale]' => 'en',
            ]
        );

        // we should be redirected to step 3
        self::assertIs200($client);
        self::assertCurrentUrlEndsWith($client, '/install/3');
    }

    private function runTroughStep3(KernelBrowser $client): void
    {
        $form = $this->getFormForSubmitButton($client, 'Next');
        $form['install_modules[modules][0]']->tick();
        $form['install_modules[modules][1]']->tick();
        $form['install_modules[modules][2]']->tick();
        $form['install_modules[modules][3]']->tick();
        $form['install_modules[modules][4]']->tick();
        $form['install_modules[modules][5]']->tick();
        $form['install_modules[modules][6]']->tick();
        $form['install_modules[modules][7]']->tick();
        $this->submitForm($client, $form);

        // we should be redirected to step 4
        self::assertIs200($client);
        self::assertCurrentUrlEndsWith($client, '/install/4');
    }

    private function runTroughStep4(KernelBrowser $client): void
    {
        // first submit with incorrect data
        $form = $this->getFormForSubmitButton($client, 'Next');
        $this->submitForm($client, $form);
        self::assertGreaterThan(
            0,
            $client->getCrawler()->filter('div.alert-danger:contains("Problem with database credentials")')->count()
        );

        // submit with correct database credentials
        $form = $this->getFormForSubmitButton($client, 'Next');

        $this->submitForm($client, $form, [
            'install_database' => [
                'databaseHostname' => $_ENV,
            ],
        ], true);

        // we should be redirected to step 5
        self::assertIs200($client);
        self::assertCurrentUrlEndsWith($client, '/install/5');
    }

    private function runTroughStep5(KernelBrowser $client): void
    {
        $form = $this->getFormForSubmitButton($client, 'Finish installation');
        $this->submitForm(
            $client,
            $form,
            [
                'install_login[email]' => 'test@test.com',
                'install_login[password][first]' => 'password',
                'install_login[password][second]' => 'password',
            ],
            true
        );

        // we should be redirected to step 6
        self::assertIs200($client);
        self::assertCurrentUrlEndsWith($client, '/install/6');
        self::assertGreaterThan(
            0,
            $client->getCrawler()->filter('h3:contains("Installation complete")')->count()
        );
    }

    /**
     * Copies the .env.local file to a backup version.
     */
    private function backupLocalEnvFile(): void
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($this->rootDir . '/.env.local')) {
            $filesystem->copy(
                $this->rootDir . '/.env.local',
                $this->rootDir . '/.env.local~backup'
            );
        }

        if ($filesystem->exists($this->rootDir . '/var/cache/test')) {
            $filesystem->remove($this->rootDir . '/var/cache/test');
        }

        if ($filesystem->exists($this->rootDir . '/../var/cache/test_install')) {
            $filesystem->remove($this->rootDir . '/../var/cache/test_install');
        }
    }

    /**
     * Puts the backed up .env.local file back.
     */
    private function putLocalEnvFileBack(): void
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($this->rootDir . '/.env.local~backup')) {
            $filesystem->copy(
                $this->rootDir . '/.env.local~backup',
                $this->rootDir . '/.env.local',
                true
            );
            $filesystem->remove($this->rootDir . '/.env.local~backup');
        }

        if ($filesystem->exists($this->rootDir . '/var/cache/test')) {
            $filesystem->remove($this->rootDir . '/var/cache/test');
        }

        if ($filesystem->exists($this->rootDir . '/../var/cache/test_install')) {
            $filesystem->remove($this->rootDir . '/../var/cache/test_install');
        }
    }
}
