<?php

declare(strict_types=1);

namespace DMT\MailService\Adapters;

use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Exceptions\SendMessageException;
use DMT\MailService\Model\EmailMessage;

interface MailAdapterInterface
{
    /**
     * Send an email message.
     *
     * @throws InvalidMessageException
     * @throws SendMessageException
     */
    public function send(EmailMessage $message): void;
}
