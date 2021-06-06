<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository")
 * @ORM\Table(name="modules")
 */
final class Module
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $installedOn;

    private function __construct(string $name, DateTimeImmutable $installedOn)
    {
        $this->name = $name;
        $this->installedOn = $installedOn;
    }

    public static function fromString(string $name): self
    {
        return new self($name, new DateTimeImmutable());
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInstalledOn(): DateTimeImmutable
    {
        return $this->installedOn;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
