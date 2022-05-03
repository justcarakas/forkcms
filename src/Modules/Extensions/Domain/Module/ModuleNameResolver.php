<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ModuleNameResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === ModuleName::class;
    }

    /** @return Generator<ModuleName> */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        if ($argument->isNullable() && !$request->attributes->has($argument->getName())) {
            yield null;
        } else {
            yield ModuleName::fromString($request->attributes->get($argument->getName()));
        }
    }
}
