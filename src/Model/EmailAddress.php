<?php

declare(strict_types=1);

namespace DMT\MailService\Model;

use Stringable;

final class EmailAddress implements Stringable
{
    public function __construct(
        public readonly string $email,
        public ?string $name = null,
    ) {
    }

    public function __toString(): string
    {
        if (!empty($this->name)) {
            return $this->name . ' <' . $this->email . '>';
        }

        return $this->email;
    }
}
