<?php

namespace ForkCMS\Modules\Backend\Domain\Widget;

use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

/** @ORM\Embeddable */
final class ModuleWidget implements Stringable
{
    /**
     * @ORM\Column(type="modules__extensions__module__module_name")
     */
    private ModuleName $module;

    /**
     * @ORM\Column(type="modules__backend__widget__widget_name")
     */
    private WidgetName $widget;

    public function __construct(ModuleName $module, WidgetName $widget)
    {
        $this->module = $module;
        $this->widget = $widget;
        Assert::that($this->getFQCN())->classExists('Widget class not found');
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (!preg_match(
            '/^ForkCMS\\\Modules\\\([A-Z]\w*)\\\Backend\\\Widgets\\\([A-Z]\w*$)/',
            $fullyQualifiedClassName,
            $matches
        )) {
            throw new InvalidArgumentException('Can ony be created from a backen widget class name');
        }

        return new self(ModuleName::fromString($matches[1]), WidgetName::fromString($matches[2]));
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->module . '\\Backend\\Widgets\\' . $this->widget;
    }

    public function __toString(): string
    {
        return $this->getFQCN();
    }

    public function getModule(): ModuleName
    {
        return $this->module;
    }

    public function getWidget(): WidgetName
    {
        return $this->widget;
    }

    public function asRole(): string
    {
        $identifier = Container::underscore($this->module->getName()) . '__' .
                      Container::underscore($this->widget->getName());

        return 'ROLE_MODULE_WIDGET__' . strtoupper($identifier);
    }
}
