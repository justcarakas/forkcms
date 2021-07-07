<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSetting;

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

    /**
     * @var Collection<string, ModuleSetting>|ModuleSetting[]
     *
     * @Orm\OneToMany(
     *     targetEntity="ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSetting",
     *     mappedBy="module",
     *     indexBy="key",
     *     cascade={"persist", "remove"}
     * )
     */
    private Collection $settings;

    private function __construct(ModuleName $name, DateTimeImmutable $installedOn)
    {
        $this->name = $name;
        $this->installedOn = $installedOn;
        $this->settings = new ArrayCollection();
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

    /**
     * @return Collection<string, ModuleSetting>|ModuleSetting[]
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function __toString(): string
    {
        return $this->name->getName();
    }
}
