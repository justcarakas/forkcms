<?php

namespace ForkCMS\Core\Domain\Meta;

use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;

final class SEOFollowType extends ValueObjectDBALType
{
    public const NAME = 'core__meta__seo_follow';

    protected function fromString(string $value): SEOFollow
    {
        return SEOFollow::from($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
