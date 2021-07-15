<?php

namespace ForkCMS\Core\Tests\Domain\Header;

use ForkCMS\Core\Domain\Header\JsData;
use ForkCMS\Modules\Blog\Installer\BlogInstaller;
use PHPUnit\Framework\TestCase;

class JsDataTest extends TestCase
{
    public function testInitialData(): void
    {
        $data = ['language' => 'en'];

        self::assertStringContainsString(json_encode($data), (string) new JsData($data));
    }

    public function testJavascriptAssignment(): void
    {
        $jsData = new JsData();

        self::assertStringContainsString('<script>var jsData = ', (string) $jsData);
        self::assertStringContainsString('</script>', (string) $jsData);
    }

    public function testAddingData(): void
    {
        $jsData = new JsData();

        $jsData->add(BlogInstaller::getModuleName(), 'lorem', 'ipsum');

        self::assertStringContainsString(
            json_encode([BlogInstaller::getModuleName()->getName() => ['lorem' => 'ipsum']]),
            (string) $jsData
        );
    }
}
