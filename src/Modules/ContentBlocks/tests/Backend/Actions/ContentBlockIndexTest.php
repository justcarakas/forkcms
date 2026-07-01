<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\tests\Backend\Actions;

use ForkCMS\Modules\Backend\tests\BackendWebTestCase;
use ForkCMS\Modules\ContentBlocks\DataFixtures\ContentBlockFixture;

final class ContentBlockIndexTest extends BackendWebTestCase
{
    protected const string TEST_URL = '/private/en/content-blocks/content-block-index';

    public function testPageLoads(): void
    {
        self::loginBackendUser();
        self::assertPageLoadedCorrectly(
            self::TEST_URL,
            'Content blocks | Modules | Fork CMS | Fork CMS',
            ['Title', 'Visible on site', ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE, ContentBlockFixture::CONTENT_BLOCK_HIDDEN_TITLE]
        );
        self::assertHasLink('Add', '/private/en/content-blocks/content-block-add');
    }

    public function testDataGrid(): void
    {
        self::loadPage();
        self::assertDataGridHasLink(ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
        self::assertDataGridHasLink(ContentBlockFixture::CONTENT_BLOCK_HIDDEN_TITLE);
        self::filterDataGrid('ContentBlock.title', ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
        self::assertDataGridHasLink(ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
        self::assertDataGridNotHasLink(ContentBlockFixture::CONTENT_BLOCK_HIDDEN_TITLE);
        self::filterDataGrid('ContentBlock.title', 'nothing to see here');
        self::assertDataGridIsEmpty();
    }

    #[\Override]
    protected static function getClassFixtures(): array
    {
        return [
            new ContentBlockFixture(),
        ];
    }
}
