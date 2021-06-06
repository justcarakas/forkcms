<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Assert\Assertion;

final class ModuleName
{
    public const NAME_VALIDATION_REGEX = '/^[A-Z][A-Za-z0-9]*$/';

    private string $name;

    private function __construct(string $name)
    {
        Assertion::regex($name, self::NAME_VALIDATION_REGEX, 'Invalid module name');

        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
