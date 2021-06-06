<?php

namespace ForkCMS\Modules\Locale\Backend\Domain\Locale;

use ForkCMS\Core\Domain\Locale\Locale as CommonLocale;

final class Locale
{
    public static function workingLocale(): self
    {
        return self::make(Language::getWorkingLanguage());
    }

    protected function getPossibleLanguages(): array
    {
        return Language::getWorkingLanguages();
    }
}
