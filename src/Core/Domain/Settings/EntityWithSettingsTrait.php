<?php

namespace ForkCMS\Core\Domain\Settings;

use Doctrine\ORM\Mapping as ORM;

trait EntityWithSettingsTrait
{
    #[ORM\Column(type: SettingsBagDBALType::class)]
    private(set) SettingsBag $settings;

    public function hasSetting(string $name): bool
    {
        return $this->settings->has($name);
    }

    public function removeSetting(string $name): void
    {
        $this->settings->remove($name);
    }

    public function getSetting(string $name, mixed $default = null): mixed
    {
        return $this->settings->getOr($name, $default);
    }

    public function setSetting(string $name, mixed $value): void
    {
        $this->settings->set($name, $value);
    }
}
