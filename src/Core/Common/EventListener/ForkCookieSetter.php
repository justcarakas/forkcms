<?php

namespace Common\EventListener;

use Common\Core\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ForkCookieSetter
{
    /** @var Cookie */
    private $cookie;

    public function __construct(Cookie $cookie)
    {
        $this->cookie = $cookie;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->cookie->attachToResponse($event->getResponse());
    }
}
