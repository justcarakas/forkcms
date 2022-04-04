<?php

namespace ForkCMS\Modules\Frontend\Domain\Block;

use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Type: string implements TranslatableInterface
{
    case ACTION = 'action';
    case WIDGET = 'widget';

    public function getDirectoryName(): string
    {
        return match (true) {
            $this === self::ACTION => 'Actions',
            $this === self::WIDGET => 'Widgets',
        };
    }

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return (match (true) {
            $this === self::ACTION => TranslationKey::label('Action'),
            $this === self::WIDGET => TranslationKey::label('Widget'),
        })->trans($translator, $locale);
    }
}
