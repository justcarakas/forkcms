<?php

namespace ForkCMS\Modules\Frontend\Domain\AjaxAction;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AjaxActionSlugResolver implements ValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === AjaxActionSlug::class;
    }

    /** @return array<?AjaxActionSlug> */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== AjaxActionSlug::class) {
            return [];
        }

        $ajaxActionSlug = AjaxActionSlug::fromRequest($request);

        return [$ajaxActionSlug];
    }
}
