<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class LocaleDBALType extends ValueObjectDBALType
{
    protected function fromString(string $value): Stringable
    {
        return Locale::from($value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(5)';
    }
}
