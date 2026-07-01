<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use Stringable;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module implements Stringable
{
    use Blameable;

    use EntityWithSettingsTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: ModuleNameDBALType::class)]
        public readonly ModuleName $name
    ) {
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

    public function __toString(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return realpath(__DIR__ . '/../../../../Modules/' . $this->name);
    }

    public function getAssetsPath(): string
    {
        return $this->getPath() . '/assets';
    }
}
