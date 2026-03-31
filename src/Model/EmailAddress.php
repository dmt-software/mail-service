<?php

declare(strict_types=1);

namespace DMT\MailService\Model;

use Stringable;

final readonly class EmailAddress implements Stringable
{
    public function __construct(
        public string $email,
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
