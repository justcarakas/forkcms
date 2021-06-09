<?php

namespace ForkCMS\Modules\Internationalisation\Frontend\Domain\Locale;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale as CommonLocale;

final class Locale
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
