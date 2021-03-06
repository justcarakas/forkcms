<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

/** @ORM\Embeddable */
final class ModuleAction implements Stringable
{
    /**
     * @ORM\Column(type="modules__extensions__module__module_name")
     */
    private ModuleName $module;

    /**
     * @ORM\Column(type="modules__backend__action__action_name")
     */
    private ActionName $action;

    public function __construct(ModuleName $module, ActionName $action)
    {
        $this->module = $module;
        $this->action = $action;
        Assert::that($this->getFQCN())->classExists('Action class not found');
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (
            !preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z]\w*)\\\Backend\\\Actions\\\([A-Z]\w*$)/',
                $fullyQualifiedClassName,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Can ony be created from a backen action class name');
        }

        return new self(ModuleName::fromString($matches[1]), ActionName::fromString($matches[2]));
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->module . '\\Backend\\Actions\\' . $this->action;
    }

    public function __toString(): string
    {
        return $this->getFQCN();
    }

    public function getModule(): ModuleName
    {
        return $this->module;
    }

    public function getAction(): ActionName
    {
        return $this->action;
    }

    public function asRole(): string
    {
        $identifier = Container::underscore($this->module->getName()) . '__' .
                      Container::underscore($this->action->getName());

        return 'ROLE_MODULE_ACTION__' . strtoupper($identifier);
    }
}
