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
    private string $key;

    /** @ORM\Column(type="object") */
    private mixed $value;

    public function __construct(Module $module, string $key, mixed $value)
    {
        $this->module = $module;
        $this->key = $key;
        $this->value = $value;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /** @return true of the value has changed */
    public function setValue(mixed $value): bool
    {
        if ($this->value === $value) {
            return false;
        }

        $this->value = $value;

        return true;
    }
}
