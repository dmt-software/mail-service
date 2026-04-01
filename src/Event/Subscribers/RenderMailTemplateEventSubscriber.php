<?php

declare(strict_types=1);

namespace DMT\MailService\Event\Subscribers;

use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Model\EmailMessage;
use DMT\MailService\Model\TemplatedMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;
use Twig\Error\Error;

final readonly class RenderMailTemplateEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private Environment $twig)
    {
    }

    /**
     * @throws InvalidMessageException
     */
    public function renderTemplate(EmailMessage $message): void
    {
        if (!$message instanceof TemplatedMessage) {
            return;
        }

        try {
            $template = $this->twig->load($message->template);

            if (!$template->hasBlock('html_part')) {
                $message->html = $template->render($message->context);

                return;
            }

            if ($template->hasBlock('html_part')) {
                $message->html = $template->renderBlock('html_part', $message->context);
            }

            if ($template->hasBlock('text_part')) {
                $message->text = $template->renderBlock('text_part', $message->context);
            }
        } catch (Error $exception) {
            throw new InvalidMessageException(
                'Could not render template',
                previous: $exception
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailMessage::class => ['renderTemplate', 50]
        ];
    }
}
