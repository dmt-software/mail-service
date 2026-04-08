<?php

namespace DMT\MailService;

use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Exceptions\SendMessageException;
use DMT\MailService\Model\EmailMessage;

interface MailServiceInterface
{
    /**
     * Send an email message.
     *
     * @throws InvalidMessageException
     * @throws SendMessageException
     */
    public function send(EmailMessage $emailMessage): void;
}
