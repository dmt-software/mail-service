<?php

declare(strict_types=1);

namespace DMT\MailService;

use DMT\DependencyInjection\ConfigurationInterface;
use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\ServiceProviderInterface;
use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Adapters\SymfonyMailAdapter;
use DMT\MailService\Event\Subscribers\HtmlToTextEventSubscriber;
use DMT\MailService\Event\MailServiceEventDispatcher;
use DMT\MailService\Event\Subscribers\RenderMailTemplateEventSubscriber;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Twig\Environment;

class MailServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            id: MailAdapterInterface::class,
            value: fn() => new SymfonyMailAdapter(
                new Mailer(
                    Transport::fromDsn(
                        $container->get(id: ConfigurationInterface::class)->get('mailer.dsn', 'null://null')
                    ),
                ),
            )
        );

        $container->set(
            id: MailServiceEventDispatcher::class,
            value: fn() => new MailServiceEventDispatcher(
                $container->get(HtmlToTextEventSubscriber::class),
                $container->get(RenderMailTemplateEventSubscriber::class)
            )
        );

        $container->set(
            id: MailServiceInterface::class,
            value: fn(): MailServiceInterface => $container->get(MailService::class)
        );
    }
}
