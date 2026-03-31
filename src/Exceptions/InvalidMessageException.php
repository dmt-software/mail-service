<?php

declare(strict_types=1);

namespace DMT\MailService\Exceptions;

use RuntimeException;

class InvalidMessageException extends RuntimeException implements ExceptionInterface
{
}
