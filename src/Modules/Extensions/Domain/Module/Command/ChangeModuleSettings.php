<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Module\Command;

use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;

/** Can't be readonly because of the __set method */
final class ChangeModuleSettings
{
    /** @param array<string, mixed> $defaults */
    public function __construct(
        public readonly Module $core,
        public readonly Module $module,
        private readonly array $defaults,
    ) {
    }

    public function __get(string $key): mixed
    {
        $module = $this->getConvertedModule($key);
        $defaults = $this->getConvertedDefault($key);
        $key = $this->getConvertedKey($key);
        if ($module->settings->has($key)) {
            return $module->settings->get($key);
        }

        if (array_key_exists($key, $defaults)) {
            return $defaults[$key];
        }

        $matches = [];
        if (preg_match($this->getLocaleAgnosticRegexMatch(), $key, $matches)) {
            return $defaults[$matches[1]] ?? null;
        }

        return null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->getConvertedModule($key)->settings->set($this->getConvertedKey($key), $value);
    }

    public function __isset(string $key)
    {
        if ($this->getConvertedModule($key)->settings->has($this->getConvertedKey($key))) {
            return true;
        }

        $defaults = $this->getConvertedDefault($key);
        $key = $this->getConvertedKey($key);
        $matches = [];

        return array_key_exists($key, $defaults) || (
            preg_match($this->getLocaleAgnosticRegexMatch(), $key, $matches)
            && array_key_exists($matches[1], $defaults)
        );
    }

    private function getLocaleAgnosticRegexMatch(): string
    {
        static $regex = null;
        if ($regex === null) {
            $localeRegex = implode('|', array_map(static fn (Locale $locale) => $locale->value, Locale::cases()));

            $regex = '/^(.*?)_(' . $localeRegex . ')$/';
        }

        return $regex;
    }

    private function getConvertedModule(string $key): Module
    {
        if (str_starts_with($key, 'core:')) {
            return $this->core;
        }

        return $this->module;
    }

    private function getConvertedKey(string $key): string
    {
        if (str_starts_with($key, 'core:')) {
            return substr($key, 5);
        }

        return $key;
    }

    /** @return array<string, mixed> $defaults */
    private function getConvertedDefault(string $key): array
    {
        if (str_starts_with($key, 'core:')) {
            return $this->defaults['core'] ?? [];
        }

        return $this->defaults;
    }
}
