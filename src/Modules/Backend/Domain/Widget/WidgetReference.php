<?php

namespace ForkCMS\Modules\Backend\Domain\Widget;

use Assert\Assert;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

final class WidgetReference implements Stringable
{
    private function __construct(private ModuleName $moduleName, private WidgetName $widgetName)
    {
        Assert::that($this->getFQCN())->classExists('Widget class not found');
    }

    public static function fromReference(string $reference): self
    {
        $matches = [];
        if (
            !preg_match(
                '#(^[a-z][a-z0-9_]*[a-z0-9]*)/([a-z][a-z0-9_]*[a-z0-9]*$)#',
                $reference,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Reference could not be matched to a module and an widget');
        }

        return new self(
            ModuleName::fromString(Container::camelize($matches[1])),
            WidgetName::fromString(Container::camelize($matches[2]))
        );
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (
            !preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z]\w*)\\\Backend\\\Widgets\\\([A-Z]\w*$)/',
                $fullyQualifiedClassName,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Can ony be created from a backen widget class name');
        }

        return new self(ModuleName::fromString($matches[1]), WidgetName::fromString($matches[2]));
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->moduleName . '\\Backend\\Widgets\\' . $this->widgetName;
    }

    public static function fromModuleWidget(ModuleWidget $moduleWidget): self
    {
        return new self($moduleWidget->getModule(), $moduleWidget->getWidget());
    }

    public function asModuleWidget(): ModuleWidget
    {
        return new ModuleWidget($this->getModuleName(), $this->getWidgetName());
    }

    public function getReference(): string
    {
        return implode(
            '/',
            [
                Container::underscore($this->moduleName->getName()),
                Container::underscore($this->widgetName->getName()),
            ]
        );
    }

    public function __toString(): string
    {
        return $this->getReference();
    }

    public function getModuleName(): ModuleName
    {
        return $this->moduleName;
    }

    public function getWidgetName(): WidgetName
    {
        return $this->widgetName;
    }
}
