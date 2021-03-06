<?php

namespace ForkCMS\Modules\Extensions\Domain\ModuleSetting;

use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Extensions\Domain\Module\Module;

/**
 * @ORM\Entity(repositoryClass="ModuleSettingRepository")
 * @ORM\Table(name="module_settings")
 */
class ModuleSetting
{
    /**
     * @ORM\Id
     * @Orm\ManyToOne(targetEntity="ForkCMS\Modules\Extensions\Domain\Module\Module", inversedBy="settings")
     * @Orm\JoinColumn(referencedColumnName="name")
     */
    private Module $module;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private string $name;

    /** @ORM\Column(type="object", name="serialisedValue") */
    private mixed $value;

    public function __construct(Module $module, string $name, mixed $value)
    {
        $this->module = $module;
        $this->name = $name;
        $this->value = $value;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /** @return bool true of the value has changed */
    public function setValue(mixed $value): bool
    {
        if ($this->value === $value) {
            return false;
        }

        $this->value = $value;

        return true;
    }
}
