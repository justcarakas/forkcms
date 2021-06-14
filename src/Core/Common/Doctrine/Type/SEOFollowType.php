<?php

namespace ForkCMS\Core\Common\Doctrine\Type;

use ForkCMS\Core\Common\Doctrine\ValueObject\SEOFollow;
use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class SEOFollowType extends ValueObjectDBALType
{
    protected function fromValue(string $value): Stringable
    {
        return SEOFollow::fromString($value);
    }

    public function getName(): string
    {
        return 'seo_follow';
    }
}
