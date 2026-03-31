<?php

declare(strict_types=1);

namespace DMT\MailService\EventSubscribers;

use DMT\MailService\Model\TemplatedMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class RenderMailTemplateEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TemplatedMessage::class => [
                'listener' => 'renderTemplate',
                'priority' => 50,
            ]
        ];
    }

    public function __construct(private Environment $twig)
    {
    }

    public function renderTemplate(TemplatedMessage $mail): void
    {
        $template = $this->twig->load($mail->template);

        if (!$template->hasBlock('html-part')) {
            $mail->html = $template->render($mail->context);

            return;
        }

        if ($template->hasBlock('html-part')) {
            $mail->html = $template->renderBlock('html-part', $mail->context);
        }

        if ($template->hasBlock('text-part')) {
            $mail->text = $template->renderBlock('text-part', $mail->context);
        }
    }
}
