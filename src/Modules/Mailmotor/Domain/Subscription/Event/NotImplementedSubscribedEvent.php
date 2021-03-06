<?php

namespace ForkCMS\Modules\Mailmotor\Domain\Subscription\Event;

use ForkCMS\Modules\Mailmotor\Domain\Subscription\Command\Subscription;
use Symfony\Contracts\EventDispatcher\Event;

final class NotImplementedSubscribedEvent extends Event
{
    const EVENT_NAME = 'mailmotor.event.not_implemented.subscribed';

    /**
     * @var Subscription
     */
    private $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
