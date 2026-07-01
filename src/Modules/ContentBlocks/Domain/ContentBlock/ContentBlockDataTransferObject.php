<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ContentBlockDataTransferObject
{
    protected ?ContentBlock $contentBlockEntity;

    public int $id;

    public Block $widget;

    public int $revisionId;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public string $title;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public string $template = ContentBlock::DEFAULT_TEMPLATE;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $text;

    public bool $isVisible = true;

    public Locale $locale;

    public Status $status;

    public SettingsBag $settings;

    public function __construct(?ContentBlock $contentBlockEntity = null)
    {
        $this->contentBlockEntity = $contentBlockEntity;

        if (!$contentBlockEntity instanceof ContentBlock) {
            $this->status = Status::ACTIVE;
            $this->settings = new SettingsBag();

            return;
        }

        $this->id = $contentBlockEntity->id;
        $this->widget = $contentBlockEntity->widget;
        $this->isVisible = !$contentBlockEntity->isHidden;
        $this->title = $contentBlockEntity->title;
        $this->text = $contentBlockEntity->text;
        $this->template = $contentBlockEntity->template;
        $this->locale = $contentBlockEntity->locale;
        $this->status = $contentBlockEntity->status;
        $this->revisionId = $contentBlockEntity->revisionId;
        $this->settings = $contentBlockEntity->settings;
    }

    public function getEntity(): ?ContentBlock
    {
        return $this->contentBlockEntity;
    }
}
