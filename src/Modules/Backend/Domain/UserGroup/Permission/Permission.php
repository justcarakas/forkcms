<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Permission;

use Stringable;

final readonly class Permission implements Stringable
{
    public function __construct(
        public string $value,
        public string $module,
        public string $name,
        public string $description
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
