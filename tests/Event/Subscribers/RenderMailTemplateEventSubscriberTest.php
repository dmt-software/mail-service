<?php

declare(strict_types=1);

namespace DMT\Test\MailService\Event\Subscribers;

use DMT\MailService\Event\Subscribers\RenderMailTemplateEventSubscriber;
use DMT\MailService\Model\TemplatedMessage;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class RenderMailTemplateEventSubscriberTest extends TestCase
{
    public function testRenderHtml(): void
    {
        $twig = new Environment(new ArrayLoader([
            'mail/no-blocks.html.twig' => 'Hello {{ name }}',
        ]));

        $mail = new TemplatedMessage(
            subject: 'test mail template',
            template: 'mail/no-blocks.html.twig',
            context: [
                'name' => 'Jane',
            ],
        );

        new RenderMailTemplateEventSubscriber($twig)->renderTemplate($mail);

        $this->assertSame('Hello Jane', $mail->html);
        $this->assertEmpty($mail->text);
    }

    public function testRenderHtmlAndTextPart(): void
    {
        $twig = new Environment(new ArrayLoader([
            'mail/with-blocks.html.twig' => <<<'TWIG'
{% block html_part %}
<h1>Hello {{ name }}</h1>
{% endblock %}

{% block text_part %}
Hello {{ name }}
{% endblock %}
TWIG,
        ]));

        $mail = new TemplatedMessage(
            subject: 'test mail template',
            template: 'mail/with-blocks.html.twig',
            context: [
                'name' => 'Jane',
            ],
        );

        new RenderMailTemplateEventSubscriber($twig)->renderTemplate($mail);

        $this->assertStringContainsString('<h1>Hello Jane</h1>', $mail->html);
        $this->assertStringContainsString('Hello Jane', $mail->text);
    }

    public function testRenderHtmlPartOnly(): void
    {
        $twig = new Environment(new ArrayLoader([
            'mail/html-only.html.twig' => <<<'TWIG'
{% block html_part %}
<p>Welcome {{ name }}</p>
{% endblock %}
TWIG,
        ]));

        $mail = new TemplatedMessage(
            subject: 'test mail template',
            template: 'mail/html-only.html.twig',
            context: [
                'name' => 'Jane',
            ],
        );

        new RenderMailTemplateEventSubscriber($twig)->renderTemplate($mail);

        $this->assertStringContainsString('<p>Welcome Jane</p>', $mail->html);
        $this->assertEmpty($mail->text);
    }
}
