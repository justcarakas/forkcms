<?php

namespace ForkCMS\Core\Domain\Twig;

use ForkCMS\Modules\Backend\Domain\Action\ActionName;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Frontend\Domain\Block\BlockName;
use ForkCMS\Modules\Frontend\Domain\Block\BlockRouter;
use ForkCMS\Modules\Frontend\Domain\Block\ModuleBlock;
use ForkCMS\Modules\Frontend\Domain\Block\Type;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use InvalidArgumentException;
use Symfony\Bridge\Twig\Extension\RoutingExtension as TwigBridgeRoutingExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RoutingExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $generator,
        private readonly TwigBridgeRoutingExtension $twigBridgeRoutingExcension,
        private readonly RequestStack $requestStack,
        private readonly BlockRouter $blockRouter,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'action_url',
                [$this, 'getActionUrl'],
                ['is_safe_callback' => [$this->twigBridgeRoutingExcension, 'isUrlGenerationSafe']]
            ),
            new TwigFunction(
                'action_path',
                [$this, 'getActionPath'],
                ['is_safe_callback' => [$this->twigBridgeRoutingExcension, 'isUrlGenerationSafe']]
            ),
            new TwigFunction(
                'block_url',
                [$this, 'getBlockUrl']
            ),
        ];
    }

    /** @param array<string,mixed> $parameters */
    public function getActionPath(
        string|ActionName|null $actionName = null,
        string|ModuleName|null $moduleName = null,
        array $parameters = [],
        bool $relative = false,
        ?string $locale = null
    ): string {
        return $this->getActionSlug($moduleName, $actionName)->generateRoute(
            $this->generator,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH,
            $locale === null ? null : Locale::tryFrom($locale)
        );
    }

    /** @param array<string,mixed> $parameters */
    public function getActionUrl(
        string|ActionName|null $actionName = null,
        string|ModuleName|null $moduleName = null,
        array $parameters = [],
        bool $relative = false,
        ?string $locale = null
    ): string {
        return $this->getActionSlug($moduleName, $actionName)->generateRoute(
            $this->generator,
            $parameters,
            $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL,
            $locale === null ? null : Locale::tryFrom($locale)
        );
    }

    /** @param array<string,mixed> $parameters */
    public function getBlockUrl(
        string|ModuleName $moduleName,
        string|BlockName $blockName,
        string|Type $type = Type::ACTION,
        array $parameters = [],
        bool $relative = false,
        ?string $locale = null
    ): string {
        return $this->blockRouter->getRouteForBlock(
            $this->getModuleBlock($moduleName, $blockName, $type),
            $locale === null ? null : Locale::tryFrom($locale),
            $parameters,
            $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    private function getActionSlug(string|ModuleName|null $moduleName, string|ActionName|null $actionName): ActionSlug
    {
        if ($moduleName === null || $actionName === null) {
            $request = $this->requestStack->getMainRequest() ?? throw new InvalidArgumentException(
                'Module name and action name are required when there is no active request.'
            );
            $defaultSlug = ActionSlug::fromRequest($request);
            $moduleName ??= $defaultSlug->getModuleName();
            $actionName ??= $defaultSlug->getActionName();
        }

        if (is_string($moduleName)) {
            $moduleName = ModuleName::fromString($moduleName);
        }

        if (is_string($actionName)) {
            $actionName = ActionName::fromString($actionName);
        }

        return new ActionSlug($moduleName, $actionName);
    }

    private function getModuleBlock(
        ModuleName|string $moduleName,
        BlockName|string $blockName,
        Type|string $type
    ): ModuleBlock {
        if (is_string($moduleName)) {
            $moduleName = ModuleName::fromString($moduleName);
        }

        if (is_string($blockName)) {
            $blockName = (is_string($type) ? Type::from($type) : $type)->getBlockName($blockName);
        }

        return new ModuleBlock($moduleName, $blockName);
    }
}
