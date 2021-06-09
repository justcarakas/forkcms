<?php

namespace ForkCMS\Modules\Internationalisation\Backend\Domain\Locale;


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
