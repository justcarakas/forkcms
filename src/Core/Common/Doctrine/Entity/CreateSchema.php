<?php

namespace ForkCMS\Core\Common\Doctrine\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;

class CreateSchema
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Adds a new doctrine entity in the database
     *
     * @param string $entityClass
     */
    public function forEntityClass(string $entityClass): void
    {
        $this->forEntityClasses([$entityClass]);
    }

    /**
     * Adds new doctrine entities in the database
     *
     * @param string[] $entityClasses
     *
     * @throws ToolsException
     */
    public function forEntityClasses(array $entityClasses): void
    {
        $schemaTool = new SchemaTool($this->entityManager);

        $schemaTool->updateSchema(
            array_map(
                [$this->entityManager, 'getClassMetadata'],
                $entityClasses
            ),
            true
        );
    }
}
