<?php

namespace DMT\MailService\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailServiceEventDispatcher extends EventDispatcher
{
    public function __construct(EventSubscriberInterface ...$eventSubscribers)
    {
        parent::__construct();

        foreach ($eventSubscribers as $eventSubscriber) {
            $this->addSubscriber($eventSubscriber);
        }
    }
}
