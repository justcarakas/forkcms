<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ActionSlugResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === ActionSlug::class;
    }

    /** @return Generator<ActionSlug> */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        yield ActionSlug::fromRequest($request);
    }
}
