<?php

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
        $url = SafeString::fromXML($author->url);

        return new self(
            SafeString::fromXML($author->name),
            filter_var($url->string, FILTER_VALIDATE_URL) !== false ? $url : null
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
