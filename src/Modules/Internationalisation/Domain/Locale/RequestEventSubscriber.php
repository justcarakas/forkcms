<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestEventSubscriber
{
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
    public function mainRequestLocale(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        Locale::current(Locale::from($event->getRequest()->getLocale()));
    }
}
