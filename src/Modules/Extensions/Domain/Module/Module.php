<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository")
 * @ORM\Table(name="modules")
 */
class Module
{
    /**
     * @ORM\Id
     * @ORM\Column(type="modules__extensions__module__module_name")
     */
    private ModuleName $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $installedOn;

    private function __construct(ModuleName $name, DateTimeImmutable $installedOn)
    {
        $this->name = $name;
        $this->installedOn = $installedOn;
    }

    public static function fromString(string $name): self
    {
        return new self(ModuleName::fromString($name), new DateTimeImmutable());
    }

    public static function fromModuleName(ModuleName $moduleName): self
    {
        return new self($moduleName, new DateTimeImmutable());
    }

    public function getName(): ModuleName
    {
        return $this->name;
    }

    public function getInstalledOn(): DateTimeImmutable
    {
        return $this->installedOn;
    }

    public function __toString(): string
    {
        return $this->name->getName();
    }
}
