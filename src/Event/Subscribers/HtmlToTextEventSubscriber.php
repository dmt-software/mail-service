<?php

declare(strict_types=1);

namespace DMT\MailService\Event;

use DMT\MailService\Model\EmailMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HtmlToTextEventSubscriber implements EventSubscriberInterface
{
    public function onSendMail(EmailMessage $message): void
    {
        if (!empty($message->text)) {
            return;
        }

        $text = html_entity_decode($message->html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $replacements = [
            '~(?<=\n)\s+~' => fn() => '',
            '~<(head|style)\b.*?</\1>~is' => fn() => '',
            '~<br.*?/?>~is' => fn() => "\n",
            '~<(h\d)\b.*?>(.*?)</\1>~is' => fn($m) => sprintf("%s\n___\n", $m[2]),
            '~<(a)\b href=("|\')([^\2]*)\2.*?>(.*?)</\1>~is' => fn($m) => sprintf('%s (%s)', $m[4], $m[3]),
        ];

        $text = preg_replace_callback_array($replacements, $text);

        $message->text = trim(strip_tags($text));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailMessage::class => ['onSendMail', 0]
        ];
    }
}