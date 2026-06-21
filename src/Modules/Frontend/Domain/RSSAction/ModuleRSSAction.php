<?php

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use Assert\Assert;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Stringable;

final class ModuleRSSAction implements Stringable
{
    private ModuleName $module;

    private RSSActionName $action;

    public function __construct(ModuleName $module, RSSActionName $action)
    {
        $this->module = $module;
        $this->action = $action;
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
            throw new InvalidArgumentException('Can ony be created from a frontend RSS action class name');
        }

        return new self(ModuleName::fromString($matches[1]), RSSActionName::fromString($matches[2]));
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->module . '\\Frontend\\RSS\\' . $this->action;
    }

    public function __toString(): string
    {
        return $this->getFQCN();
    }

    public function getModule(): ModuleName
    {
        return $this->module;
    }

    public function getAction(): RSSActionName
    {
        return $this->action;
    }
}
