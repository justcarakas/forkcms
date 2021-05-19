<?php

namespace ForkCMS\Core\Common;

use InvalidArgumentException;
use JsonSerializable;
use Serializable;

abstract class Locale implements Serializable, JsonSerializable
{
    protected string $locale;

    final protected function __construct(string $locale)
    {
        $this->setLocale($locale);
    }

    /**
     * @return static
     */
    final public static function fromString(string $locale): self
    {
        return new static($locale);
    }

    abstract protected function getPossibleLanguages(): array;

    /**
     * @param string $locale
     *
     * @return static
     */
    final protected function setLocale(string $locale): self
    {
        if (!array_key_exists($locale, $this->getPossibleLanguages())) {
            throw new InvalidArgumentException('Invalid language');
        }

        $this->locale = $locale;

        return $this;
    }

    final public function __toString(): string
    {
        return $this->locale;
    }

    final public function getLocale(): string
    {
        return $this->locale;
    }

    final public function serialize(): string
    {
        return $this->locale;
    }

    final public function unserialize($serialized): void
    {
        $this->setLocale($serialized);
    }

    final public function equals(Locale $locale): bool
    {
        return $this->locale === $locale->locale;
    }

    final public function jsonSerialize(): string
    {
        return $this->locale;
    }
}
