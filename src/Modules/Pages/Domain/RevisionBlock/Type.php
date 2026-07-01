<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Pages\Domain\RevisionBlock;

use ForkCMS\Core\Domain\Enum\TryFromNullable;
use ForkCMS\Modules\Frontend\Domain\Block\Type as BlockType;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;

enum Type: string
{
    use TryFromNullable;

    case EDITOR = 'editor';
    case ACTION = 'action';
    case WIDGET = 'widget';

    public function getLabel(): TranslationKey
    {
        return TranslationKey::label(ucfirst($this->value));
    }

    public static function fromBlockType(?BlockType $type): self
    {
        return self::tryFromNullable($type?->value) ?? self::EDITOR;
    }

    /** @return array<value-of<self>, self> */
    public static function formTypeChoices(): array
    {
        return array_combine(array_column(self::cases(), 'value'), self::cases());
    }

    public function isEditor(): bool
    {
        return $this === self::EDITOR;
    }
}
