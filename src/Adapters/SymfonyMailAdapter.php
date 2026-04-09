<?php

declare(strict_types=1);

namespace DMT\MailService\Adapters;

use DMT\DependencyInjection\Attributes\ConfigValue;
use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Exceptions\SendMessageException;
use DMT\MailService\Model\EmailMessage;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;

final readonly class SymfonyMailAdapter implements MailAdapterInterface
{
    private MailerInterface $mailer;

    public function __construct(
        #[ConfigValue('mailer.dsn', 'null://null')]
        ?string $dsn = null,
        ?MailerInterface $mailer = null
    ) {
        $this->mailer = $mailer ?? new Mailer(Transport::fromDsn($dsn));
    }

    /**
     * @inheritDoc
     */
    public function send(EmailMessage $message): void
    {
        if ($message->to === null) {
            throw new InvalidMessageException('Mail must contain at least one recipient.');
        }

        if ($message->html === null && $message->text === null) {
            throw new InvalidMessageException('Mail must contain either HTML or text content.');
        }

        try {
            $email = new Email()
                ->to((string)$message->to)
                ->subject($message->subject ?? '');

            if ($message->from !== null) {
                $email->from((string)$message->from);
            }

            if ($message->replyTo !== null) {
                $email->replyTo((string)$message->replyTo);
            }
        } catch (RfcComplianceException $exception) {
            throw new InvalidMessageException($exception->getMessage());
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
            throw new SendMessageException(
                sprintf('Mail could not be sent: %s', $exception->getMessage()),
                previous: $exception,
            );
        }
    }
}
