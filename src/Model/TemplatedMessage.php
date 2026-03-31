<?php

declare(strict_types=1);

namespace DMT\MailService\Model;

class TemplatedMessage extends EmailMessage
{
    public function __construct(
        string $subject,
        public readonly string $template,
        ?EmailAddress $to = null,
        ?EmailAddress $from = null,
        ?EmailAddress $replyTo = null,
        public array $context = [],
    ) {
        parent::__construct(
            subject: $subject,
            to: $to,
            from: $from,
            replyTo: $replyTo
        );
    }
}
