<?php

namespace ForkCMS\Modules\Backend\Domain\AjaxAction;

use ForkCMS\Modules\Backend\Backend\Ajax\NotFound;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AjaxActionSlugResolver implements ArgumentValueResolverInterface
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === AjaxActionSlug::class;
    }

    /** @return Generator<AjaxActionSlug> */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $ajaxAxtionSlug = AjaxActionSlug::fromRequest($request);
        if (!$this->authorizationChecker->isGranted($ajaxAxtionSlug->asModuleAction()->asRole())) {
            yield NotFound::getAjaxActionSlug();
        } else {
            yield $ajaxAxtionSlug;
        }
    }
}
