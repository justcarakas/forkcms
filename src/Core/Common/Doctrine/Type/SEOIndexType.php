<?php

namespace ForkCMS\Core\Common\Doctrine\Type;

use ForkCMS\Core\Common\Doctrine\ValueObject\SEOIndex;
use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class SEOIndexType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return SEOIndex::fromString($value);
    }

    public function getName(): string
    {
        return 'seo_index';
    }
}
