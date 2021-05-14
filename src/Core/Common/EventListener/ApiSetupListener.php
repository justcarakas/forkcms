<?php

namespace Common\EventListener;

use Backend\Core\Engine\Backend;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApiSetupListener
{
    /** @var KernelInterface */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (strpos($event->getRequest()->getPathInfo(), '/api/') === 0) {
            $application = new Backend($this->kernel);
            $application->passContainerToModels();
        }
    }
}
