<?php

namespace ForkCMS\Core\Domain\Application;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ForkCMS\Modules\Backend\Domain\Navigation\ValueObjectDBALType;
use Stringable;

final class ApplicationDBALType extends ValueObjectDBALType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(10)';
    }

    protected function fromValue(string $value): Stringable
    {
        return Application::from($value);
    }
}
