<?php

declare(strict_types=1);

namespace DMT\MailService\Tests\Adapters;

use DMT\MailService\Adapters\SymfonyMailAdapter;
use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Exceptions\SendMessageException;
use DMT\MailService\Model\EmailAddress;
use DMT\MailService\Model\EmailMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class SymfonyMailAdapterTest extends TestCase
{
    public function testMissingRecipient(): void
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Mail must contain at least one recipient.');

        $mailer = $this->createMock(MailerInterface::class);
        $adapter = new SymfonyMailAdapter($mailer);

        $message = new EmailMessage(
            subject: 'Test subject',
            html: '<p>Hello</p>',
            text: null,
            to: null,
        );

        $adapter->send($message);
    }

    public function testMissingMailContent(): void
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Mail must contain either HTML or text content.');

        $mailer = $this->createMock(MailerInterface::class);
        $adapter = new SymfonyMailAdapter($mailer);

        $message = new EmailMessage(
            subject: 'Test subject',
            html: null,
            text: null,
            to: new EmailAddress('user@example.com'),
        );

        $adapter->send($message);
    }

    public function testSendEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (mixed $email): bool {
                $this->assertInstanceOf(Email::class, $email);
                $this->assertCount(1, $email->getTo());

                return true;
            }));

        $message = new EmailMessage(
            subject: 'Test subject',
            html: '<p>Hello Jane</p>',
            text: 'Hello Jane',
            to: new EmailAddress('user@example.com', 'User'),
            from: new EmailAddress('from@example.com'),
            replyTo: new EmailAddress('reply@example.com'),
        );

        new SymfonyMailAdapter($mailer)->send($message);
    }

    public function testFailSendingEmail(): void
    {
        $this->expectException(SendMessageException::class);
        $this->expectExceptionMessage('Transport failed');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new TransportException('Transport failed'));

        $message = new EmailMessage(
            subject: 'Test subject',
            html: '<p>Hello</p>',
            text: null,
            to: new EmailAddress('user@example.com', 'User'),
        );

        new SymfonyMailAdapter($mailer)->send($message);
    }
}
