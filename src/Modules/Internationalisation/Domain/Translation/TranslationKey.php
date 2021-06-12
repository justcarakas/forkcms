<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Container;

/** @ORM\Embeddable */
class TranslationKey
{
    /**
     * @ORM\Column(type="modules_internationalisation_type")
     */
    private Type $type;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    private function __construct(Type $type, string $name)
    {
        $this->type = $type;
        Assert::that($name)->same(Container::camelize($name), 'The name should be in CamilCase');
        $this->name = Container::camelize($name);
    }

    public static function forType(Type $type, string $name): self
    {
        return new self($type, $name);
    }

    public static function label(string $name): self
    {
        return new self(Type::label(), $name);
    }

    public static function message(string $name): self
    {
        return new self(Type::message(), $name);
    }

    public static function error(string $name): self
    {
        return new self(Type::error(), $name);
    }

    public static function slug(string $name): self
    {
        return new self(Type::slug(), $name);
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->type->value . '.' . $this->name;
    }
}
