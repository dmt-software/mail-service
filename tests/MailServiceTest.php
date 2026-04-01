<?php

declare(strict_types=1);

namespace DMT\Test\MailService;

use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Event\MailServiceEventDispatcher;
use DMT\MailService\Event\Subscribers\HtmlToTextEventSubscriber;
use DMT\MailService\Event\Subscribers\RenderMailTemplateEventSubscriber;
use DMT\MailService\MailService;
use DMT\MailService\Model\EmailAddress;
use DMT\MailService\Model\EmailMessage;
use DMT\MailService\Model\TemplatedMessage;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class MailServiceTest extends TestCase
{
    public function testSendEmailMessage(): void
    {
        $adapter = $this->createMock(MailAdapterInterface::class);
        $adapter->expects($this->once())->method('send');

        $message = new EmailMessage(
            subject: 'Test mail',
            html: '<div><p>Test mail</p></div>',
            to: new EmailAddress('receiver@example.com'),
            from: new EmailAddress('sender@example.com'),
        );

        new MailService($adapter, $this->getMailServiceEventDispatcher())->send($message);

        $this->assertSame('Test mail', $message->text);
    }

    public function testSendTemplatedMessage(): void
    {
        $adapter = $this->createMock(MailAdapterInterface::class);
        $adapter->expects($this->once())->method('send');

        $message = new TemplatedMessage(
            subject: 'Test mail',
            template: 'mail/test-mail.twig',
            to: new EmailAddress('receiver@example.com'),
            from: new EmailAddress('sender@example.com'),
            context: ['firstName' => 'John', 'lastName' => 'Doe'],
        );

        new MailService($adapter, $this->getMailServiceEventDispatcher())->send($message);

        $this->assertSame('<b>Welcome John</b>', $message->html);
        $this->assertSame('Welcome John', $message->text);
    }

    private function getMailServiceEventDispatcher(): MailServiceEventDispatcher
    {
        return new MailServiceEventDispatcher(
            new HtmlToTextEventSubscriber(),
            new RenderMailTemplateEventSubscriber(
                new Environment(new ArrayLoader([
                    'mail/test-mail.twig' => '<b>Welcome {{ firstName }}</b>'
                ]))
            )
        );
    }
}
