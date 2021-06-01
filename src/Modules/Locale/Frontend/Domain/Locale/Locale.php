<?php

namespace ForkCMS\Modules\Locale\Frontend\Domain\Locale;

use ForkCMS\Core\Domain\Locale\Locale as CommonLocale;

final class Locale extends CommonLocale
{
    public static function frontendLanguage(): ?self
    {
        if (\defined('FRONTEND_LANGUAGE')) {
            return new self(FRONTEND_LANGUAGE);
        }

        return null;
    }

    protected function getPossibleLanguages(): array
    {
        return array_flip(Language::getActiveLanguages());
    }
}
