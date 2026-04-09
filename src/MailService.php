<?php

declare(strict_types=1);

namespace DMT\MailService;

use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Event\MailServiceEventDispatcher;
use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Exceptions\SendMessageException;
use DMT\MailService\Model\EmailMessage;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class MailService
{
    public function __construct(
        private MailAdapterInterface $adapter,
        private EventDispatcherInterface $eventDispatcher = new MailServiceEventDispatcher(),
    ) {
    }

    /**
     * Send an email message.
     *
     * @throws InvalidMessageException
     * @throws SendMessageException
     */
    public function send(EmailMessage $emailMessage): void
    {
        /** @var EmailMessage $emailMessage */
        $emailMessage = $this->eventDispatcher->dispatch($emailMessage, EmailMessage::class);

        $this->adapter->send($emailMessage);
    }
}
