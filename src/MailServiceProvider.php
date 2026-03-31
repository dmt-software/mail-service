<?php

declare(strict_types=1);

namespace DMT\MailService;

use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\ServiceProviderInterface;
use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Adapters\SymfonyMailAdapter;
use DMT\MailService\Event\HtmlToTextEventSubscriber;
use DMT\MailService\Event\MailServiceEventDispatcher;
use DMT\MailService\Event\RenderMailTemplateEventSubscriber;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Twig\Environment;

class MailServiceProvider implements ServiceProviderInterface
{
    public function __construct(private array $config = [])
    {
    }

    public function register(Container $container): void
    {
        $container->set(
            id: MailAdapterInterface::class,
            value: fn() => new SymfonyMailAdapter(
                new Mailer(Transport::fromDsn($this->config['dsn'] ?? 'null://null')),
            )
        );

        $container->set(
            id: RenderMailTemplateEventSubscriber::class,
            value: fn() => new RenderMailTemplateEventSubscriber($container->get(Environment::class))
        );

        $container->set(
            id: MailServiceEventDispatcher::class,
            value: fn() => new MailServiceEventDispatcher(
                $container->get(HtmlToTextEventSubscriber::class),
                $container->get(RenderMailTemplateEventSubscriber::class)
            )
        );

        $container->set(
            id: MailService::class,
            value: fn() => new MailService(
                $container->get(MailAdapterInterface::class),
                $container->get(MailServiceEventDispatcher::class),
            )
        );
    }
}
