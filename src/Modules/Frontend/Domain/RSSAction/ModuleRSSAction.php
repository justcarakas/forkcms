<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use Assert\Assert;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Stringable;

final readonly class ModuleRSSAction implements Stringable
{
    public function __construct(
        private(set) ModuleName $module,
        private(set) RSSActionName $action,
    ) {
        Assert::that($this->getFQCN())->classExists('RSS action class not found');
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (
            !preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z]\w*)\\\Frontend\\\RSS\\\([A-Z]\w*$)/',
                $fullyQualifiedClassName,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Can only be created from a frontend RSS action class name');
        }

        return new self(ModuleName::fromString($matches[1]), RSSActionName::fromString($matches[2]));
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->module . '\\Frontend\\RSS\\' . $this->action;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getFQCN();
    }
}
