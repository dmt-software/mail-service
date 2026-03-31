<?php

declare(strict_types=1);

namespace DMT\MailService\Adapters;

use DMT\MailService\Model\EmailMessage;

interface MailAdapterInterface
{
    public function send(EmailMessage $message): void;
}
