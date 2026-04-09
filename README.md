# Mail Service

## Installation

```bash
composer require dmt-software/mail-service
```

### Choose a mailer package

Currently, the only adapter available uses `symfony/mailer`.  
This should be added to the composer dependencies.

```bash
composer require symfony/mailer:^8.0
```

## Usage

```php
use DMT\MailService\Exceptions\InvalidMessageException;
use DMT\MailService\Exceptions\SendMessageException;
use DMT\MailService\MailService;
use DMT\MailService\Model\EmailAddress;
use DMT\MailService\Model\EmailMessage;

try {
    /** @var MailService $service */
    $service->send(
        new EmailMessage(
            subject: 'subject',
            html: '<p>html content</p>',
            text: 'text content',
            to: new EmailAddress('user@example.com'),
            from: new EmailAddress('site@example.com') 
        )
    );
} catch (InvalidMessageException|SendMessageException) {
    // error sending email
}
```

## Events

### Auto create text part

To create a text part of the email, the `HtmlToTextEventSubscriber` can be used. For any HTML mailing without a text 
part, this event subscriber will generate a simple text variant for that message. 


```php
use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Event\MailServiceEventDispatcher;
use DMT\MailService\Event\Subscribers\HtmlToTextEventSubscriber;
use DMT\MailService\MailService;
use DMT\MailService\Model\EmailMessage;

/** @var MailAdapterInterface $adapter */
$service = new MailService(
    $adapter, 
    new MailServiceEventDispatcher(
        new HtmlToTextEventSubscriber()
    )
);

/** @var EmailMessage $message */
$message = new EmailMessage(
    subject: 'subject',
    html: '<p>content</p>',
    text: null,
    to: new EmailAddress('user@example.com'),
    from: new EmailAddress('site@example.com') 
);

$service->send($message); // message will contain a text part: "content"
```  

### Using mail templates

The message content can also be rendered from a twig template. This can be done by using the 
`RenderMailTemplateEventSubscriber` subscriber.

```php
use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\MailService\Event\MailServiceEventDispatcher;
use DMT\MailService\Event\Subscribers\RenderMailTemplateEventSubscriber;
use DMT\MailService\MailService;
use DMT\MailService\Model\TemplatedMessage;
use Twig\Environment;

/** @var MailAdapterInterface $adapter */
/** @var Environment $twig */
$service = new MailService(
    $adapter, 
    new MailServiceEventDispatcher(
        new RenderMailTemplateEventSubscriber($twig)
    )
);

$message = new TemplatedMessage(
    subject: 'subject',
    template: 'mail/template.twig',
    to: new EmailAddress('user@example.com'),
    from: new EmailAddress('site@example.com'),
    context: ['name' => 'Jane Doe']
);

$service->send($message); // template will be rendered
```

If a template contains a block called **html_part**, that block will be rendered for the HTML part of the message. A 
block called **text_part**, will be rendered as text part of the message. Both can be present at the same time. When 
none of the blocks above are specified the whole template is rendered as HTML part.
