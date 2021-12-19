<?php

namespace ForkCMS\Core\Domain\Meta;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;

final class SEOIndexType extends ValueObjectDBALType
{
    public const NAME = 'core__meta__seo_index';

    protected function fromString(string $value): SEOIndex
    {
        return SEOIndex::from($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
