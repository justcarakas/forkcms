<?php

namespace ForkCMS\Core\Tests\Domain\Header;

use ForkCMS\Core\Domain\Header\Header;
use ForkCMS\Core\Domain\Header\JsData;
use ForkCMS\Core\Domain\Kernel\Kernel;
use ForkCMS\Modules\Blog\Installer\BlogInstaller;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class HeaderTest extends TestCase
{
    private Header $header;

    protected function setUp(): void
    {
        parent::setUp();

        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/en/blog'));
        $this->header = new Header($requestStack, new Kernel('prod', true));
    }

    public function testAddJsData(): void
    {
        $headerJsDataProperty = new ReflectionProperty(Header::class, 'jsData');
        $headerJsDataProperty->setAccessible(true);
        $jsDataProperty = new ReflectionProperty(JsData::class, 'jsData');
        $jsDataProperty->setAccessible(true);
        $this->header->addJsData(BlogInstaller::getModuleName(), 'key', 'value');
        $jsData = $jsDataProperty->getValue($headerJsDataProperty->getValue($this->header));
        $this->assertIsArray($jsData);
        $this->assertArrayHasKey(BlogInstaller::getModuleName()->getName(), $jsData);
    }

    public function testParse(): void
    {
        $jsDataProperty = new ReflectionProperty(Header::class, 'jsData');
        $jsDataProperty->setAccessible(true);
        $twig = $this->createPartialMock(Environment::class, ['addGlobal']);
        $twig->expects($this->once())->method('addGlobal')->with('jsData', $jsDataProperty->getValue($this->header));
        $this->header->parse($twig);
    }
}
