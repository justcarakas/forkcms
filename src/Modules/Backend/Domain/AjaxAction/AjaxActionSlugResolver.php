<?php

namespace ForkCMS\Modules\Backend\Domain\AjaxAction;

use ForkCMS\Modules\Backend\Backend\Ajax\Forbidden;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class AjaxActionSlugResolver implements ValueResolverInterface
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

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
        if (!$this->authorizationChecker->isGranted($ajaxActionSlug->asModuleAction()->asRole())) {
            return [Forbidden::getAjaxActionSlug()];
        }

        return [$ajaxActionSlug];
    }
}
