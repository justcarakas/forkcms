<?php

namespace ForkCMS\Modules\Pages\Domain\Revision;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\RevisionBlock\RevisionBlockDataTransferObject;

abstract class RevisionDataTransferObject
{
    public ?Page $page = null;
    public ?Page $parentPage = null;
    public MenuType $type = MenuType::ROOT;
    public ?string $title = null;
    public bool $isDraft = false;
    public ?ThemeTemplate $themeTemplate = null;
    public ?DateTimeImmutable $archivedOn = null;
    /** @var ArrayCollection<string, non-empty-list<RevisionBlockDataTransferObject>> */
    public ArrayCollection $blocks;
    public ?Locale $locale = null;
    /** @var array<string, mixed>  */
    public array $settings = [];
    public ?Meta $meta = null;
    protected ?Revision $revisionEntity;

    public function __construct(?Revision $revisionEntity = null)
    {
        $this->revisionEntity = $revisionEntity;

        if ($this->revisionEntity === null) {
            $this->blocks = new ArrayCollection();

            return;
        }

        $this->page = $this->revisionEntity->page;
        $this->parentPage = $this->revisionEntity->parentPage;
        $this->type = $this->revisionEntity->type;
        $this->title = $this->revisionEntity->title;
        $this->isDraft = $this->revisionEntity->isDraft;
        $this->themeTemplate = $this->revisionEntity->themeTemplate;
        $this->archivedOn = $this->revisionEntity->archivedOn;
        $blocks = [];
        foreach ($this->revisionEntity->blocks as $block) {
            $position = $block->position;
            if (!array_key_exists($position, $blocks)) {
                $blocks[$position] = [];
            }
            $blocks[$position][] = new RevisionBlockDataTransferObject($block);
        }
        $this->blocks = new ArrayCollection($blocks);
        $this->locale = $this->revisionEntity->getLocale();
        $this->settings = $this->revisionEntity->getSettings()->all();
        $this->meta = clone $this->revisionEntity->meta;
    }

    public function hasEntity(): bool
    {
        return $this->revisionEntity !== null;
    }

    public function getEntity(): Revision
    {
        return $this->revisionEntity;
    }

    public function addBlock(string $position, Block $block): void
    {
        $revisionBlock = new RevisionBlockDataTransferObject();
        $revisionBlock->block = $block;
        $revisionBlock->position = $position;
        $blocks = $this->blocks->get($position) ?? [];
        $blocks[] = $revisionBlock;
        $this->blocks->set($position, $blocks);
    }
}
