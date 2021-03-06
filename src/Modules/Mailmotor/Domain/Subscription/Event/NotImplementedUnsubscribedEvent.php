<?php

namespace ForkCMS\Modules\Mailmotor\Domain\Subscription\Event;

use ForkCMS\Modules\Mailmotor\Domain\Subscription\Command\Unsubscription;
use Symfony\Contracts\EventDispatcher\Event;

final class NotImplementedUnsubscribedEvent extends Event
{
    const EVENT_NAME = 'mailmotor.event.not_implemented.unsubscribed';

    /**
     * @var Unsubscription
     */
    private $unsubscription;

    public function __construct(Unsubscription $unsubscription)
    {
        $this->unsubscription = $unsubscription;
    }

    public function getUnsubscription(): Unsubscription
    {
        return $this->unsubscription;
    }
}
