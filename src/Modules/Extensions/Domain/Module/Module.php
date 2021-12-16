<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'modules')]
class Module
{
    use Blameable;

    #[ORM\Id]
    #[ORM\Column(type: 'modules__extensions__module__module_name')]
    private ModuleName $name;

    #[ORM\Column(type: 'core__settings__settings_bag')]
    private SettingsBag $settings;

    private function __construct(ModuleName $name)
    {
        $this->name = $name;
        $this->settings = new SettingsBag();
    }

    public static function fromString(string $name): self
    {
        return new self(ModuleName::fromString($name));
    }

    public static function fromModuleName(ModuleName $moduleName): self
    {
        return new self($moduleName);
    }

    public function getName(): ModuleName
    {
        return $this->name;
    }

    public function getSettings(): SettingsBag
    {
        return $this->settings;
    }

    public function __toString(): string
    {
        return $this->name->getName();
    }
}
