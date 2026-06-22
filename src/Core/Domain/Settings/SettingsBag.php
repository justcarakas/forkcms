<?php

namespace ForkCMS\Core\Domain\Settings;

use DateTimeImmutable;
use DateTimeZone;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use JsonSerializable;

final class SettingsBag implements JsonSerializable
{
    /** @var array<string, mixed> */
    private array $settings = [];

    private bool $hasChanges = false;

    private static ?Locale $locale = null;

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings = [])
    {
        $this->add($settings);
    }

    public static function setDefaultLocale(Locale $locale): void
    {
        self::$locale = $locale;
    }

    public function clear(): void
    {
        if (count($this->settings) > 0) {
            $this->hasChanges = true;
        }

        $this->settings = [];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function add(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->settings;
    }

    public function get(string $name, Locale $locale = null): mixed
    {
        $localisedName = self::getLocalisedName($name, $locale);

        if ($localisedName !== $name && array_key_exists($localisedName, $this->settings)) {
            return $this->settings[$localisedName];
        }

        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }

        if (!$name) {
            throw new SettingNotFoundException($name);
        }

        $this->nameNotFound($name);
    }

    public function getOr(string $name, mixed $default = null, Locale $locale = null): mixed
    {
        try {
            return $this->get($name, $locale);
        } catch (SettingNotFoundException) {
            return $default;
        }
    }

    public function set(string $name, mixed $value, Locale $locale = null): void
    {
        // check if the value is a json encoded datetime
        if (
            is_array($value)
            && count($value) === 3
            && isset($value['date'], $value['timezone'], $value['timezone_type'])
            && $value['timezone_type'] === 3
        ) {
            $value = new DateTimeImmutable($value['date'], new DateTimeZone($value['timezone']));
        }

        $localisedName = self::getLocalisedName($name, $locale);
        if (array_key_exists($localisedName, $this->settings)) {
            $this->hasChanges &= $this->settings[$localisedName] !== $value;
            $this->settings[$localisedName] = $value;
            return;
        }

        if (!array_key_exists($name, $this->settings) || $this->settings[$name] !== $value) {
            $this->hasChanges = true;
        }

        $this->settings[$name] = $value;
    }

    public function has(string $name): bool
    {
        if (array_key_exists($name, $this->settings)) {
            return true;
        }

        $localisedName = self::getLocalisedName($name);

        return $localisedName !== $name && array_key_exists($localisedName, $this->settings);
    }

    public function remove(string $name, Locale $locale = null): void
    {
        $localisedName = self::getLocalisedName($name, $locale);
        if ($localisedName !== $name && array_key_exists($localisedName, $this->settings)) {
            $this->hasChanges = true;
            unset($this->settings[$localisedName]);

            return;
        }
        if (array_key_exists($name, $this->settings)) {
            $this->hasChanges = true;
            unset($this->settings[$name]);

            return;
        }

        $this->nameNotFound($name);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->all();
    }

    public function hasChanges(): bool
    {
        return $this->hasChanges;
    }

    public function asJsonString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    public static function fromJsonString(string $value): self
    {
        return new SettingsBag(json_decode($value, true, 512, JSON_THROW_ON_ERROR));
    }

    public static function getLocalisedName(string $key, Locale $locale = null): string
    {
        if (!self::$locale) {
            return $key;
        }

        return $key . '_' . ($locale ?? self::$locale)->value;
    }

    private function nameNotFound(string $name): never
    {
        $alternatives = [];
        foreach ($this->settings as $key => $parameterValue) {
            $lev = levenshtein($name, $key);
            if ($lev <= strlen($name) / 3 || str_contains($key, $name)) {
                $alternatives[] = $key;
            }
        }

        throw new SettingNotFoundException($name, null, $alternatives);
    }
}
