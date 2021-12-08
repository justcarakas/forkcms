<?php

namespace ForkCMS\Core\Domain\Settings;

use JsonSerializable;

use function array_key_exists;
use function strlen;

final class SettingsBag implements JsonSerializable
{
    /** @var array<string, mixed>  */
    protected array $settings = [];

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->add($parameters);
    }

    public function clear(): void
    {
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

    public function get(string $name): mixed
    {
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }

        if (!$name) {
            throw new SettingNotFoundException($name);
        }

        $alternatives = [];
        foreach ($this->settings as $key => $parameterValue) {
            $lev = levenshtein($name, $key);
            if ($lev <= strlen($name) / 3 || str_contains($key, $name)) {
                $alternatives[] = $key;
            }
        }

        throw new SettingNotFoundException($name, null, $alternatives);
    }

    public function set(string $name, mixed $value): void
    {
        $this->settings[$name] = $value;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->settings);
    }

    public function remove(string $name): void
    {
        unset($this->settings[$name]);
    }

    public function jsonSerialize(): array
    {
        return $this->all();
    }
}
