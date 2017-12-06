<?php

namespace Backend\Modules\Locale\Domain\Locale\Name;

final class Name
{
    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        if ($name[0] === strtoupper($name[0])) {
            throw InvalidNameException::mustBeCapitalised();
        }

        if (preg_match('|^[a-z0-9]+$|i', $name)) {
            throw InvalidNameException::onlyAlphanumeric();
        }

        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
