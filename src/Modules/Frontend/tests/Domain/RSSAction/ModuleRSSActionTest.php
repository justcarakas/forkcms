<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\tests\Domain\RSSAction;

use ForkCMS\Modules\Frontend\Domain\RSSAction\ModuleRSSAction;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ModuleRSSActionTest extends TestCase
{
    public function testFromFQCNParsesModuleAndActionFromClassName(): void
    {
        $moduleRssAction = ModuleRSSAction::fromFQCN(
            'ForkCMS\Modules\Frontend\Frontend\RSS\NotFound'
        );

        self::assertSame('ForkCMS\Modules\Frontend\Frontend\RSS\NotFound', (string) $moduleRssAction);
        self::assertSame('Frontend', (string) $moduleRssAction->module);
        self::assertSame('NotFound', (string) $moduleRssAction->action);
    }

    public function testFromFQCNThrowsOnNonMatchingClassName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can only be created from a frontend RSS action class name');

        ModuleRSSAction::fromFQCN('ForkCMS\Modules\Frontend\Domain\RSSAction\SomeAction');
    }

    public function testFromFQCNThrowsOnNonExistentClass(): void
    {
        $this->expectException(\Throwable::class);

        ModuleRSSAction::fromFQCN('ForkCMS\Modules\Blog\Frontend\RSS\RecentArticles');
    }
}
