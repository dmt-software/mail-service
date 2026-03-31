<?php

declare(strict_types=1);

namespace DMT\Test\MailService\Event\Subscribers;

use DMT\MailService\Event\Subscribers\HtmlToTextEventSubscriber;
use DMT\MailService\Model\EmailMessage;
use PHPUnit\Framework\TestCase;

class HtmlToTextEventSubscriberTest extends TestCase
{
    public function testSkipExistingTextPart(): void
    {
        $message = new EmailMessage(
            subject: 'Test mailing',
            html: '<p>Hello <strong>world</strong></p>',
            text: 'Already set',
        );

        new HtmlToTextEventSubscriber()->onSendMail($message);

        $this->assertSame('Already set', $message->text);
    }

    public function testConvertsBasicHtmlToText(): void
    {
        $message = new EmailMessage(
            subject: 'Test mailing',
            html: '<p>Hello <strong>world</strong></p><br><p>Second line</p>',
        );

        new HtmlToTextEventSubscriber()->onSendMail($message);

        $this->assertSame("Hello world\nSecond line", $message->text);
    }

    public function testRemovesHeadAndStyleContent(): void
    {
        $message = new EmailMessage(
            subject: 'Test mailing',
            html: <<<HTML
                <html>
                    <head>
                        <title>Ignore me</title>
                        <style>.hidden { display:none; }</style>
                    </head>
                    <body>
                        <style>.visible { display:block; }</style>
                        <p>Visible content</p>
                    </body>
                </html>
                HTML,
        );

        new HtmlToTextEventSubscriber()->onSendMail($message);

        $this->assertSame('Visible content', $message->text);
    }

    public function testConvertsLinksToTextWithUrl(): void
    {
        $message = new EmailMessage(
            subject: 'Test mailing',
            html: '<p>Click <a href="https://example.com/reset">here</a> now.</p>',
        );

        new HtmlToTextEventSubscriber()->onSendMail($message);

        $this->assertSame('Click here (https://example.com/reset) now.', $message->text);
    }

    public function testDecodesHtmlEntities(): void
    {
        $message = new EmailMessage(
            subject: 'Test mailing',
            html: '<p>Tom &amp; Jerry &quot;Movie&quot;</p>',
        );

        new HtmlToTextEventSubscriber()->onSendMail($message);

        $this->assertSame('Tom & Jerry "Movie"', $message->text);
    }

    public function testConvertsHeadingsToTextBlocks(): void
    {
        $message = new EmailMessage(
            subject: 'Test mailing',
            html: '<h1>Welcome</h1><p>Hello world</p>',
        );

        new HtmlToTextEventSubscriber()->onSendMail($message);

        $this->assertSame("Welcome\n___\nHello world", $message->text);
    }
}
