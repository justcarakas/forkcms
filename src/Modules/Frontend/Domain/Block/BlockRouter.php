<?php

namespace ForkCMS\Modules\Frontend\Domain\Block;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use InvalidArgumentException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class BlockRouter
{
    public function __construct(private BlockRouterInterface $blockRouter)
    {
    }

    /** @param array<string, mixed> $parameters */
    public function getRouteForBlock(
        ModuleBlock $moduleBlock,
        ?Locale $locale = null,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if ($moduleBlock->getName()->getType() !== Type::ACTION) {
            throw new InvalidArgumentException('Only actions can be routed');
        }

        return $this->blockRouter->getRouteForBlock($moduleBlock, $locale, $parameters, $referenceType);
    }
}
