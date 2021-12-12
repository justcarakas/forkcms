<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use ForkCMS\Modules\Backend\Backend\Actions\NotFound;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ActionSlugResolver implements ArgumentValueResolverInterface
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === ActionSlug::class;
    }

    /** @return Generator<ActionSlug> */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $actionSlug = ActionSlug::fromRequest($request);
        if (!$this->authorizationChecker->isGranted($actionSlug->asModuleAction()->asRole())) {
            yield NotFound::getActionSlug();
        } else {
            yield $actionSlug;
        }
    }
}
