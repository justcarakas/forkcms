<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class TypeDBALType extends ValueObjectDBALType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(7)';
    }

    protected function fromValue(string $value): Stringable
    {
        return Type::from($value);
    }
}
