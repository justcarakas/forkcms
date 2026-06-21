<?php

namespace ForkCMS\Modules\Frontend\Controller;

use ForkCMS\Modules\Frontend\Domain\RSSAction\RSSActionControllerInterface;
use ForkCMS\Modules\Frontend\Domain\RSSAction\RSSActionSlug;
use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FrontendRSSController
{
    /** @param ServiceLocator<RSSActionControllerInterface> $rssActions */
    public function __construct(private readonly ServiceLocator $rssActions)
    {
    }

    public function __invoke(
        Request $request,
        RSSActionSlug $actionSlug
    ): Response {
        try {
            $action = $this->rssActions->get($actionSlug->getFQCN());
        } catch (NotFoundExceptionInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The rss action class %s must be registered as a service and implement %s',
                    $actionSlug->getFQCN(),
                    RSSActionControllerInterface::class
                )
            );
        }

        return $action($request);
    }
}
