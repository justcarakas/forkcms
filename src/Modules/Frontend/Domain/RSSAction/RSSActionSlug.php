<?php

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Frontend\Frontend\RSS\NotFound;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

final readonly class RSSActionSlug implements Stringable
{
    public function __construct(private(set) ModuleName $moduleName, private(set) RSSActionName $actionName)
    {
        Ensure::isExistingClass($this->getFQCN(), 'RSS action class does not exist');
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
            throw new InvalidArgumentException('Slug could not be matched to a module and a RSS action');
        }

        return new self(
            ModuleName::fromString(Container::camelize($matches[1])),
            RSSActionName::fromString(Container::camelize($matches[2]))
        );
    }

    public static function fromFQCN(string $fullyQualifiedClassName): self
    {
        $matches = [];
        if (
            !preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z]\\w*)\\\Frontend\\\RSS\\\([A-Z]\\w*)$/',
                $fullyQualifiedClassName,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Can only be created from a frontend RSS action class name');
        }

        return new self(ModuleName::fromString($matches[1]), RSSActionName::fromString($matches[2]));
    }

    public static function fromRequest(Request $request): self
    {
        if ($request->attributes->get('_route') !== 'frontend_rss') {
            throw new InvalidArgumentException('This is not a frontend RSS action request');
        }

        $module = $request->attributes->get('module');
        $action = $request->attributes->get('action');

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
        return 'ForkCMS\\Modules\\' . $this->moduleName . '\\Frontend\\RSS\\' . $this->actionName;
    }

    public static function fromModuleRSSAction(ModuleRSSAction $moduleRSSAction): self
    {
        return new self($moduleRSSAction->module, $moduleRSSAction->action);
    }

    public function asModuleAction(): ModuleRSSAction
    {
        return new ModuleRSSAction($this->moduleName, $this->actionName);
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
        return new TranslationDomain(Application::FRONTEND, $this->moduleName);
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

        return $router->generate('frontend_rss', $parameters, $referenceType);
    }
}
