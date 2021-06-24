<?php

namespace ForkCMS\Modules\Backend\Controller;

use ForkCMS\Modules\Backend\Domain\Action\ActionControllerInterface;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BackendController
{
    public function __construct(private ServiceLocator $actions)
    {
    }

    public function __invoke(
        Request $request,
        ?string $locale,
        ActionSlug $actionSlug
    ): Response {
        try {
            $action = $this->actions->get($actionSlug->getFQCN());
        } catch (NotFoundExceptionInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The action class %s must be registered as a service and implement %s',
                    $actionSlug->getFQCN(),
                    ActionControllerInterface::class
                )
            );
        }

        return $action($request);
    }
}
