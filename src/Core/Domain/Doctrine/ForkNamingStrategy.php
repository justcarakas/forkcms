<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Doctrine;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;

final class ForkNamingStrategy extends UnderscoreNamingStrategy
{
    public function __construct()
    {
        parent::__construct(CASE_LOWER);
    }

    public function propertyToColumnName(string $propertyName, string $className): string
    {
        return $propertyName;
    }

    public function classToTableName($className): string
    {
        $underscoredClassName = parent::classToTableName($className);

        try {
            $moduleName = ModuleName::fromFQCN($className)->name;
        } catch (InvalidArgumentException) {
            return $underscoredClassName;
        }

        return Container::underscore($moduleName) . '__' . $underscoredClassName;
    }

    public function joinColumnName(string $propertyName, string $className): string
    {
        return $this->propertyToColumnName($propertyName, $className);
    }

    public function joinTableName(string $sourceEntity, string $targetEntity, string $propertyName): string
    {
        $sourceModuleName = ModuleName::fromFQCN($sourceEntity);
        $targetModuleName = ModuleName::fromFQCN($targetEntity);

        if ($sourceModuleName === $targetModuleName) {
            return $this->classToTableName($sourceEntity) . '__has__' . parent::classToTableName($targetEntity);
        }
        return $this->classToTableName($sourceEntity) . '__has__' . $this->classToTableName($targetEntity);
    }

    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        if ($referencedColumnName === $this->referenceColumnName()) {
            $referencedColumnName = null;
        }

        return $this->classToTableName($entityName) . ucfirst($referencedColumnName ?: '');
    }
}
