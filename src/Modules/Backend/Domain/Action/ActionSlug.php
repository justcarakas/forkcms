<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Assert\Assertion;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Backend\Backend\Actions\AuthenticationLogin;
use ForkCMS\Modules\Backend\Backend\Actions\NotFound;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

final class ActionSlug implements Stringable
{
    private function __construct(private ModuleName $moduleName, private ActionName $actionName)
    {
        Assertion::classExists($this->getFQCN(), 'Action class does not exist');
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

    public static function fromRequest(Request $request): self
    {
        $module = $request->get('module');
        $action = $request->get('action');

        if ($module === null && $action === null) {
            return self::fromFQCN(AuthenticationLogin::class);
        }

        try {
            return self::fromSlug($module . '/' . $action);
        } catch (Throwable) {
            return self::fromFQCN(NotFound::class);
        }
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->moduleName . '\\Backend\\Actions\\' . $this->actionName;
    }

    public static function fromModuleAction(ModuleAction $moduleAction): self
    {
        return new self($moduleAction->getModule(), $moduleAction->getAction());
    }

    public function asModuleAction(): ModuleAction
    {
        return new ModuleAction($this->getModuleName(), $this->getActionName());
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

    public function getTranslationDomain(): TranslationDomain
    {
        return new TranslationDomain(Application::backend(), $this->moduleName);
    }

    public function generateRoute(
        UrlGeneratorInterface $router,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        ?Locale $locale = null
    ): string {
        $parameters = [
            'action' => Container::underscore($this->actionName->getName()),
            'module' => Container::underscore($this->moduleName->getName()),
        ];
        if ($locale instanceof Locale) {
            $parameters['_locale'] = $locale->value;
        }

        return $router->generate('backend', $parameters, $referenceType);
    }
}
