<?php

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RSSActionSlugResolver implements ValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === RSSActionSlug::class;
    }

    /** @return array<RSSActionSlug> */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== RSSActionSlug::class) {
            return [];
        }

        $rssActionSlug = RSSActionSlug::fromRequest($request);

        return [$rssActionSlug];
    }
}
