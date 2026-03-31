<?php

declare(strict_types=1);

namespace DMT\MailService\Model;

class EmailMessage
{
    public function __construct(
        public string $subject,
        public ?string $html = null,
        public ?string $text = null,
        public ?EmailAddress $to = null,
        public ?EmailAddress $from = null,
        public ?EmailAddress $replyTo = null,
    ) {
    }
}
