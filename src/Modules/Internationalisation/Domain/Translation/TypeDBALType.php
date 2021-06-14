<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use Stringable;

final class TypeDBALType extends ValueObjectDBALType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(7)';
    }

    protected function fromString(string $value): Stringable
    {
        return Type::from($value);
    }
}
