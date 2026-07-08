<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\tests\Backend\Actions;

use ForkCMS\Core\Domain\Form\LazyDataGridFieldResolver;
use ForkCMS\Modules\Backend\tests\BackendWebTestCase;
use ForkCMS\Modules\ContentBlocks\DataFixtures\ContentBlockFixture;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use Symfony\Component\HttpFoundation\Request;

final class ContentBlockEditTest extends BackendWebTestCase
{
    protected const string TEST_URL = '/private/en/content-blocks/content-block-edit/';

    private function loadContentBlock(string $title): ContentBlock
    {
        $revision = self::getEntityManager()->getRepository(Revision::class)->findOneBy(['title' => $title]);
        $contentBlock = $revision->contentBlock;
        self::loadPage(self::TEST_URL . $contentBlock->id);

        return $contentBlock;
    }

    /**
     * The revisions tab is a lazily-loaded turbo-frame (see LazyDataGridFieldResolver): the initial
     * page load only contains a placeholder, and its real content is fetched from a separate request
     * once the frame is actually shown. This mimics that fetch.
     */
    private function loadRevisionsTab(int $contentBlockId): void
    {
        self::request(
            Request::METHOD_GET,
            self::TEST_URL . $contentBlockId . '?' . LazyDataGridFieldResolver::QUERY_PARAMETER . '=revisions'
        );
    }

    public function testPageLoads(): void
    {
        $contentBlock = $this->loadContentBlock(ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
        $revision = $contentBlock->getActiveRevision();
        self::assertPageLoadedCorrectly(
            self::TEST_URL . $contentBlock->id,
            ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE . ' | Edit | Content blocks | Modules | Fork CMS | Fork CMS',
            [
                'Visible on site',
                $revision->title,
                $revision->text,
            ]
        );

        self::assertHasLink('Content blocks', '/private/en/content-blocks/content-block-index');
    }

    public function testEditWithoutChanges(): void
    {
        $contentBlock = $this->loadContentBlock(ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
        $revision = $contentBlock->getActiveRevision();
        self::assertEmptyFormSubmission('content_block', 0, 'Save');
        self::assertCurrentUrlEndsWith('/private/en/content-blocks/content-block-index');
        self::assertDataGridHasLink($revision->title);
        self::assertResponseContains('The content block "' . $revision->title . '" was saved.');
    }

    public function testRevisionsCanBeReloaded(): void
    {
        $contentBlock = $this->loadContentBlock(ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
        self::assertResponseDoesNotHaveContent('There are no previous versions yet.');
        $this->loadRevisionsTab($contentBlock->id);
        self::assertResponseContains('There are no previous versions yet.');
        self::submitForm(
            'Save',
            [
                'content_block[contentBlock][tab_Content][title]' => 'I<3ForkCMS',
                'content_block[contentBlock][tab_Content][text]' => 'It is simply amazing, you should try it too!',
            ],
        );
        self::getClient()->followRedirect();
        self::assertCurrentUrlEndsWith('/private/en/content-blocks/content-block-index');
        self::assertDataGridNotHasLink(ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
        self::assertResponseContains('The content block "I<3ForkCMS" was saved.');
        self::assertClickOnLink('I<3ForkCMS', [htmlentities('I<3ForkCMS')]);
        self::assertCurrentUrlContains(self::TEST_URL . $contentBlock->id);
        $this->loadRevisionsTab($contentBlock->id);
        self::assertResponseDoesNotHaveContent('There are no previous versions yet.');
        self::assertResponseContains('Use this version');
        self::assertResponseContains('Archived on');
        self::assertClickOnLink(
            ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE,
            ["You're using an older version. Save to overwrite the current version."]
        );
        self::assertEmptyFormSubmission('content_block', 0, 'Save');
        self::assertDataGridHasLink(ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE);
    }

    public function testUniqueness(): void
    {
        $this->loadContentBlock(ContentBlockFixture::CONTENT_BLOCK_HIDDEN_TITLE);

        self::submitForm(
            'Save',
            [
                'content_block[contentBlock][tab_Content][title]' => ContentBlockFixture::CONTENT_BLOCK_VISIBLE_TITLE,
                'content_block[contentBlock][tab_Content][text]' => 'It is simply amazing, you should try it too!',
            ],
            'This value is already used.',
        );
    }

    #[\Override]
    protected static function getClassFixtures(): array
    {
        return [
            new ContentBlockFixture(),
        ];
    }
}
