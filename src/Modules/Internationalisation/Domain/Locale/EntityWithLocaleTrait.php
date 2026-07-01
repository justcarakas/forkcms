<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait EntityWithLocaleTrait
{
    #[ORM\Column(type: Types::STRING, length: 5, enumType: Locale::class)]
    private(set) Locale $locale;
}
