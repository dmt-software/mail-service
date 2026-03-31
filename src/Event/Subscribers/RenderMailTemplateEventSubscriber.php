<?php

declare(strict_types=1);

namespace DMT\MailService\Event\Subscribers;

use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Model\TemplatedMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;
use Twig\Error\Error;

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

    /**
     * @throws InvalidMessageException
     */
    public function renderTemplate(TemplatedMessage $mail): void
    {
        try {
            $template = $this->twig->load($mail->template);

            if (!$template->hasBlock('html_part')) {
                $mail->html = $template->render($mail->context);

                return;
            }

            if ($template->hasBlock('html_part')) {
                $mail->html = $template->renderBlock('html_part', $mail->context);
            }

            if ($template->hasBlock('text_part')) {
                $mail->text = $template->renderBlock('text_part', $mail->context);
            }
        } catch (Error $exception) {
            throw new InvalidMessageException(
                'Could not render template',
                previous: $exception
            );
        }
    }
}
