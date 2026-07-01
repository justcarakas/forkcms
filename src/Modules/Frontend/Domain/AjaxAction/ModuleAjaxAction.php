<?php

namespace ForkCMS\Modules\Frontend\Domain\AjaxAction;

use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleNameDBALType;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;

#[ORM\Embeddable]
final readonly class ModuleAjaxAction implements Stringable
{
    public const string ROLE_PREFIX = 'ROLE_MODULE_AJAX_ACTION__';

    public function __construct(
        #[ORM\Column(type: ModuleNameDBALType::class)]
        public ModuleName $module,
        #[ORM\Column(type: AjaxActionNameDBALType::class)]
        public AjaxActionName $action,
    ) {
        Assert::that($this->getFQCN())->classExists('Ajax action class not found');
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (
            !preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z]\w*)\\\Frontend\\\Ajax\\\([A-Z]\w*$)/',
                $fullyQualifiedClassName,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Can only be created from a frontend ajax action class name');
        }

        return new self(ModuleName::fromString($matches[1]), AjaxActionName::fromString($matches[2]));
    }

    public static function fromRole(string $role): self
    {
        return self::tryFromRole($role)
            ?? throw new InvalidArgumentException('Role should start with: ' . self::ROLE_PREFIX);
    }

    public static function tryFromRole(string $role): ?self
    {
        if (!str_starts_with($role, self::ROLE_PREFIX)) {
            return null;
        }

        [$moduleName, $ajaxActionName] = explode('__', strtolower(substr($role, strlen(self::ROLE_PREFIX))));

        return new self(
            ModuleName::fromString(Container::camelize($moduleName)),
            AjaxActionName::fromString(Container::camelize($ajaxActionName))
        );
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->module . '\\Frontend\\Ajax\\' . $this->action;
    }

    public function __toString(): string
    {
        return $this->getFQCN();
    }

    public function asRole(): string
    {
        $identifier = Container::underscore($this->module->name) . '__' . Container::underscore($this->action->name);

        return self::ROLE_PREFIX . strtoupper($identifier);
    }
}
