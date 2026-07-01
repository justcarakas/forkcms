<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\AjaxAction;

use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Backend\Backend\Ajax\NotFound;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

final readonly class AjaxActionSlug implements Stringable
{
    public function __construct(private(set) ModuleName $moduleName, private(set) AjaxActionName $actionName)
    {
        Ensure::isExistingClass($this->getFQCN(), 'Ajax action class does not exist');
    }

    public static function fromSlug(string $slug): self
    {
        $matches = [];
        if (
            !preg_match(
                '#(^[a-z][a-z0-9_]*[a-z0-9]*)/([a-z][a-z0-9_]*[a-z0-9]*$)#',
                str_replace('-', '_', $slug),
                $matches
            )
        ) {
            throw new InvalidArgumentException('Slug could not be matched to a module and an ajax action');
        }

        return new self(
            ModuleName::fromString(Container::camelize($matches[1])),
            AjaxActionName::fromString(Container::camelize($matches[2]))
        );
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (
            !preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z]\w*)\\\Backend\\\Ajax\\\([A-Z]\w*$)/',
                $fullyQualifiedClassName,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Can only be created from a backend ajax action class name');
        }

        return new self(ModuleName::fromString($matches[1]), AjaxActionName::fromString($matches[2]));
    }

    public static function fromRequest(Request $request): self
    {
        if ($request->attributes->get('_route') !== 'backend_ajax') {
            throw new InvalidArgumentException('This is not a backend ajax action request');
        }

        $module = $request->attributes->get('module') ?? $request->request->get('module');
        $action = $request->attributes->get('action') ?? $request->request->get('action');

        if ($module === null && $action === null) {
            return self::fromFQCN(NotFound::class);
        }

        try {
            return self::fromSlug($module . '/' . $action);
        } catch (Throwable) {
            return self::fromFQCN(NotFound::class);
        }
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->moduleName . '\\Backend\\Ajax\\' . $this->actionName;
    }

    public static function fromModuleAjaxAction(ModuleAjaxAction $moduleAjaxAction): self
    {
        return new self($moduleAjaxAction->module, $moduleAjaxAction->action);
    }

    public function asModuleAction(): ModuleAjaxAction
    {
        return new ModuleAjaxAction($this->moduleName, $this->actionName);
    }

    public function getSlug(): string
    {
        return str_replace(
            '_',
            '-',
            implode(
                '/',
                [
                    Container::underscore($this->moduleName->name),
                    Container::underscore($this->actionName->name),
                ]
            )
        );
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getSlug();
    }

    public function getTranslationDomain(): TranslationDomain
    {
        return new TranslationDomain(Application::BACKEND, $this->moduleName);
    }

    /** @param array<string, mixed> $parameters */
    public function generateRoute(
        UrlGeneratorInterface $router,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        ?Locale $locale = null
    ): string {
        $parameters['action'] = str_replace('_', '-', Container::underscore($this->actionName->name));
        $parameters['module'] = str_replace('_', '-', Container::underscore($this->moduleName->name));

        if ($locale instanceof Locale) {
            $parameters['_locale'] = $locale->value;
        }

        return $router->generate('backend_ajax', $parameters, $referenceType);
    }
}
