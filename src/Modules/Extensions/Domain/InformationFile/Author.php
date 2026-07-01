<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\InformationFile;

use JsonSerializable;
use SimpleXMLElement;

final readonly class Author implements JsonSerializable
{
    public function __construct(public string $name, public ?string $url)
    {
    }

    public static function fromXML(SimpleXMLElement $author): self
    {
        $url = SafeString::fromXML($author->url)->string;

        return new self(
            SafeString::fromXML($author->name)->string,
            filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : null
        );
    }

    /** @return array<string, string|null> */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'url' => $this->url,
        ];
    }
}
