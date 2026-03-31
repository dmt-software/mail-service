<?php

declare(strict_types=1);

namespace DMT\MailService;

use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\ServiceProviderInterface;
use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Adapters\SymfonyMailAdapter;
use DMT\MailService\EventSubscribers\HtmlToTextEventSubscriber;
use DMT\MailService\EventSubscribers\RenderMailTemplateEventSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
                new Mailer(Transport::fromDsn($_ENV['MAILER_DSN'] ?? 'null://null')),
            )
        );

        $container->set(
            id: HtmlToTextEventSubscriber::class,
            value: fn() => new HtmlToTextEventSubscriber()
        );

        $container->set(
            id: RenderMailTemplateEventSubscriber::class,
            value: fn() => new RenderMailTemplateEventSubscriber($container->get(Environment::class))
        );

        $container->set(
            id: MailService::class,
            value: function() use ($container) {
                $eventDispatcher = new EventDispatcher();
                $eventDispatcher->addSubscriber($container->get(HtmlToTextEventSubscriber::class));
                $eventDispatcher->addSubscriber($container->get(RenderMailTemplateEventSubscriber::class));

                return new MailService($container->get(MailAdapterInterface::class), $eventDispatcher);
            }
        );
    }
}
