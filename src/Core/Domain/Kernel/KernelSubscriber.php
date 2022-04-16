<?php

namespace ForkCMS\Core\Domain\Kernel;

use ForkCMS\Core\Domain\Kernel\Command\ClearContainerCache;
use ForkCMS\Modules\Extensions\Domain\Module\Event\ModuleInstalledEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class KernelSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $commandBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ModuleInstalledEvent::class => 'onModuleInstalled',
        ];
    }

    public function onModuleInstalled(ModuleInstalledEvent $moduleInstalledEvent): void
    {
        $this->commandBus->dispatch(new ClearContainerCache());
    }
}
