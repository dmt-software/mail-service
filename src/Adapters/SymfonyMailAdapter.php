<?php

declare(strict_types=1);

namespace DMT\MailService\Adapters;

use DMT\MailService\Model\EmailMessage;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyMailAdapter implements MailAdapterInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function send(EmailMessage $message): void
    {
        if ($message->to === null) {
            throw new RuntimeException('Mail must contain at least one recipient.');
        }

        if ($message->html === null && $message->text === null) {
            throw new RuntimeException('Mail must contain either HTML or text content.');
        }

        $email = new Email()
            ->to((string)$message->to)
            ->subject($message->subject ?? '');

        if ($message->from !== null) {
            $email->from((string)$message->from);
        }

        if ($message->replyTo !== null) {
            $email->replyTo((string)$message->replyTo);
        }

        if ($message->html !== null) {
            $email->html($message->html);
        }

        if ($message->text !== null) {
            $email->text($message->text);
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            throw new RuntimeException(
                sprintf('Mail could not be sent: %s', $exception->getMessage()),
                previous: $exception,
            );
        }
    }
}
