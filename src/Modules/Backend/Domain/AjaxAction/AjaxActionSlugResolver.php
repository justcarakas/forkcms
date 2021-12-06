<?php

namespace ForkCMS\Modules\Backend\Domain\AjaxAction;

use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AjaxActionSlugResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === AjaxActionSlug::class;
    }

    /** @return Generator<AjaxActionSlug> */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        yield AjaxActionSlug::fromRequest($request);
    }
}
