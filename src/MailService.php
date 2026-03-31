<?php

declare(strict_types=1);

namespace DMT\MailService;

use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Model\EmailMessage;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class MailService
{
    public function __construct(
        private MailAdapterInterface $adapter,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function send(EmailMessage $emailMessage): void
    {
        /** @var EmailMessage $message */
        $message = $this->eventDispatcher->dispatch($emailMessage);

        $this->adapter->send($message);
    }
}
