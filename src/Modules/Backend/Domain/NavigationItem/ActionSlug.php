<?php

namespace ForkCMS\Modules\Backend\Domain\NavigationItem;

use Assert\Assert;
use ForkCMS\Modules\Extensions\Domain\Action\ActionName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

final class ActionSlug implements Stringable
{
    private function __construct(private ModuleName $moduleName, private ActionName $actionName)
    {
        Assert::that($this->getFQCN())->classExists('Action class not found');
    }

    public static function fromSlug(string $slug): self
    {
        $matches = [];
        if (!preg_match(
            '#(^[a-z][a-z0-9_]*[a-z0-9]*)/([a-z][a-z0-9_]*[a-z0-9]*$)#',
            $slug,
            $matches
        )) {
            throw new InvalidArgumentException('Slug could not be matched to a module and an action');
        }

        return new self(
            ModuleName::fromString(Container::camelize($matches[1])),
            ActionName::fromString(Container::camelize($matches[2]))
        );
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (!preg_match(
            '/^ForkCMS\\\Modules\\\([A-Z]\w*)\\\Backend\\\Actions\\\([A-Z]\w*$)/',
            $fullyQualifiedClassName,
            $matches
        )) {
            throw new InvalidArgumentException('Can ony be created from a backen action class name');
        }

        return new self(ModuleName::fromString($matches[1]), ActionName::fromString($matches[2]));
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->moduleName . '\\Backend\\Actions\\' . $this->actionName;
    }

    public function getSlug(): string
    {
        return implode(
            '/',
            [
                Container::underscore($this->moduleName->getName()),
                Container::underscore($this->actionName->getName()),
            ]
        );
    }

    public function __toString(): string
    {
        return $this->getSlug();
    }

    public function getModuleName(): ModuleName
    {
        return $this->moduleName;
    }

    public function getActionName(): ActionName
    {
        return $this->actionName;
    }
}
