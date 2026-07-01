<?php

namespace ForkCMS\Core\Domain\Form\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class UniqueDataTransferObject extends Constraint
{
    public const string NOT_UNIQUE_ERROR = '23bd9dbf-6b9b-41cd-a99e-4844bcf3077f';

    public string $message = 'err.NotUnique';

    public string $service = UniqueDataTransferObjectValidator::class;

    /** @var class-string|null */
    public ?string $entityClass = null;

    public string $repositoryMethod = 'findBy';

    /** @var string[]|string */
    public array|string $fields = [];

    public ?string $errorPath = null;

    public bool $ignoreNull = true;

    /** @var array<string, string> */
    protected static array $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    /**
     * @param string[]|string $fields
     * @param class-string|null $entityClass
     */
    public function __construct(
        array|string $fields = [],
        ?string $entityClass = null,
        string $repositoryMethod = 'findBy',
        ?string $errorPath = null,
        bool $ignoreNull = true,
        string $message = 'err.NotUnique',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
        $this->fields = $fields;
        $this->entityClass = $entityClass;
        $this->repositoryMethod = $repositoryMethod;
        $this->errorPath = $errorPath;
        $this->ignoreNull = $ignoreNull;
        $this->message = $message;
    }

    /** @return string[] */
    public function getRequiredOptions(): array
    {
        return ['fields'];
    }

    public function validatedBy(): string
    {
        return $this->service;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getDefaultOption(): string
    {
        return 'fields';
    }
}
