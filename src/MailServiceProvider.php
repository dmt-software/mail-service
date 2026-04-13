<?php

declare(strict_types=1);

namespace DMT\MailService;

use DMT\DependencyInjection\Attributes\ConfigValue;
use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\ServiceProviderInterface;
use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Adapters\SymfonyMailAdapter;
use DMT\MailService\Event\Subscribers\HtmlToTextEventSubscriber;
use DMT\MailService\Event\MailServiceEventDispatcher;
use DMT\MailService\Event\Subscribers\RenderMailTemplateEventSubscriber;

class MailServiceProvider implements ServiceProviderInterface
{
    public function __construct(
        #[ConfigValue('mailer.mailAdapter', SymfonyMailAdapter::class)]
        private string $mailAdapter = SymfonyMailAdapter::class
    ) {
    }

    public function register(Container $container): void
    {
        $container->set(
            id: MailAdapterInterface::class,
            value: fn (): MailAdapterInterface => $container->get($this->mailAdapter),
        );

        $container->set(
            id: MailServiceEventDispatcher::class,
            value: function () use ($container): MailServiceEventDispatcher {
                $dispatcher = new MailServiceEventDispatcher(
                    $container->get(HtmlToTextEventSubscriber::class),
                );

                if (class_exists('Twig\Environment')) {
                    $dispatcher->addSubscriber(
                        $container->get(RenderMailTemplateEventSubscriber::class),
                    );
                }

                return $dispatcher;
            }
        );

        $container->set(
            id: MailService::class,
            value: fn(): MailService => $container->get(
                MailService::class,
                eventDispatcher: $container->get(MailServiceEventDispatcher::class)
            )
        );
    }
}
