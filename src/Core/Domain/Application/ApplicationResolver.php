<?php

namespace ForkCMS\Core\Domain\Application;

use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ApplicationResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Application::class;
    }

    /** @return Generator<Application> */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        if (!$argument->isNullable()) {
            yield Application::from(strtolower($request->attributes->get($argument->getName())));
        } elseif ($request->attributes->has('application')) {
            yield Application::tryFrom(strtolower($request->attributes->get($argument->getName())));
        }
    }
}
