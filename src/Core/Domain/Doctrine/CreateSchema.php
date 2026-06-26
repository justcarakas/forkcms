<?php

namespace ForkCMS\Core\Domain\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

final class CreateSchema
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Adds new doctrine entities in the database.
     */
    public function forEntityClasses(string ...$entityClasses): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metaData = array_map(
            [$this->entityManager, 'getClassMetadata'],
            $entityClasses
        );

        $connection = $this->entityManager->getConnection();
        $config = $connection->getConfiguration();
        $previousFilter = $config->getSchemaAssetsFilter();
        // ORM 3 removed $saveMode — setting a non-null filter causes createSchemaForComparison
        // to only include tables present in the target schema, preventing DROP statements
        // for tables owned by other module installers.
        $config->setSchemaAssetsFilter(static fn (): bool => false);

        try {
            $schemaTool->updateSchema($metaData);
        } finally {
            $config->setSchemaAssetsFilter($previousFilter);
        }
    }
}
