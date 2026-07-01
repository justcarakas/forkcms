<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Kernel;

use ForkCMS\Core\Domain\Kernel\Command\ClearContainerCache;
use ForkCMS\Core\Domain\Kernel\Event\ClearCacheEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final readonly class KernelSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ClearCacheEvent::class => 'onCacheClear',
        ];
    }

    public function onCacheClear(): void
    {
        $this->commandBus->dispatch(
            (new Envelope(new ClearContainerCache()))->with(new DispatchAfterCurrentBusStamp())
        );
    }
}
