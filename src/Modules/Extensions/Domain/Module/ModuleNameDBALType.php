<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ModuleNameDBALType extends StringType
{
    public function convertToPHPValue($moduleName, AbstractPlatform $platform): ModuleName
    {
        return ModuleName::fromString($moduleName);
    }

    public function convertToDatabaseValue($moduleName, AbstractPlatform $platform): string
    {
        return (string) $moduleName;
    }

    public function getName(): string
    {
        return 'extensions_module_name';
    }
}
