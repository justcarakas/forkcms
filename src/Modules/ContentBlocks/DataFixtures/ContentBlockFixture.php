<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command\CreateContentBlockRevision;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use ForkCMS\Modules\ContentBlocks\Frontend\Widgets\ContentBlock as ContentBlockWidget;
use ForkCMS\Modules\Extensions\tests\ForkFixture;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Block\ModuleBlock;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;

final class ContentBlockFixture extends ForkFixture
{
    public const int CONTENT_BLOCK_VISIBLE_ID = 1;
    public const string CONTENT_BLOCK_VISIBLE_TITLE = 'Visible content block';
    public const string CONTENT_BLOCK_VISIBLE_TEXT = 'This is a visible content block';
    public const int CONTENT_BLOCK_HIDDEN_ID = 2;
    public const string CONTENT_BLOCK_HIDDEN_TITLE = 'Hidden content block';
    public const string CONTENT_BLOCK_HIDDEN_TEXT = 'This is a hidden content block';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            $this->createContentBlockRevision(
                self::CONTENT_BLOCK_VISIBLE_TITLE,
                self::CONTENT_BLOCK_VISIBLE_TEXT
            )
        );
        $manager->persist(
            $this->createContentBlockRevision(
                self::CONTENT_BLOCK_HIDDEN_TITLE,
                self::CONTENT_BLOCK_HIDDEN_TEXT,
                false
            )
        );

        $manager->flush();
    }

    private function createContentBlockRevision(
        string $title,
        string $text,
        bool $isEnabled = true,
    ): Revision {
        $createContentBlockRevision = CreateContentBlockRevision::new(Locale::ENGLISH);
        $createContentBlockRevision->title = $title;
        $createContentBlockRevision->text = $text;
        $createContentBlockRevision->isEnabled = $isEnabled;

        $contentBlock = new ContentBlock(
            new Block(
                ModuleBlock::fromFQCN(ContentBlockWidget::class),
                enabled: $isEnabled,
                locale: $createContentBlockRevision->locale
            ),
            $createContentBlockRevision->locale
        );

        return Revision::fromDataTransferObject($createContentBlockRevision, $contentBlock);
    }
}
