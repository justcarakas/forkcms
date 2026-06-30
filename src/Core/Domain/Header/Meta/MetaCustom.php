<?php

namespace ForkCMS\Core\Domain\Header\Meta;

final readonly class MetaCustom
{
    public string $uniqueKey;

    private function __construct(private string $metaData)
    {
        $this->uniqueKey = hash('xxh128', $this->metaData);
    }

    public function __toString(): string
    {
        return $this->metaData;
    }

    public static function fromString(string $metaData): self
    {
        return new self($metaData);
    }
}
