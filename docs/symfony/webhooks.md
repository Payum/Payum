# Receive webhooks

A payment service provider (PSP) tells you about a payment by calling a URL on your site. PayumBundle
serves that URL, checks the message is genuine, and hands it to the gateway's notify handler. With
Symfony's Webhook, RemoteEvent and Messenger components it can also put the message on a queue, so you
answer the PSP at once and handle the payment later.

This page is about the Symfony side. [Webhooks](../gateways/webhooks.md) shows how to write the notify
handler and [Payment notifications](../instant-payment-notification.md) explains verification. The
bundle needs payum/core 2.0 or newer for everything below. Gateways that still run on Payum 1 always
handle the message in the request.

## The two URLs

The bundle's notify routes are in `@PayumBundle/Resources/config/routing/notify.yaml`, which
`routing/all.yaml` includes:

| Route | Path | Used by |
| --- | --- | --- |
| `payum_notify_do` | `/payment/notify/{payum_token}` | PSPs that take a notify URL for each payment |
| `payum_notify_do_unsafe` | `/payment/notify/unsafe/{gateway}` | PSPs with one webhook endpoint |

The gateway creates the token URL for you when it asks the PSP for a payment, with
`$context->notifyUrl()`. You never build it by hand.

For a PSP with one endpoint, paste the full URL into the PSP's dashboard, with the name of the gateway
from your configuration:

```text
https://example.com/payment/notify/unsafe/stripe
```

"Unsafe" means the URL says nothing about the payment. The gateway has to verify the message itself and
find the payment from what it says.

## Keep the signing secret in the gateway options

The secret the PSP signs its messages with is an ordinary gateway option. Read it from the environment:

```yaml
# config/packages/payum.yaml
payum:
    gateways:
        stripe:
            factory: stripe
            secret_key: '%env(STRIPE_SECRET_KEY)%'
            webhook_secret: '%env(STRIPE_WEBHOOK_SECRET)%'
```

The option names belong to the gateway. Gateways you
[configure in the backend](configure-payment-in-backend.md) take the same option in their stored config,
and it is [encrypted with the rest](encrypt-gateway-configs-stored-in-database.md).

## What the controller does

For a gateway that verifies messages before handling them, `NotifyController` runs these steps:

1. It asks the gateway to parse and verify the request.
2. A message that fails verification gets `400` with an empty body.
3. A verified message is handled, now or on a queue.
4. The response is the acknowledgement the gateway chose for the PSP. A gateway that chose none gets
   `204`.

For a gateway that verifies while handling (a Payum 1 notify action), the controller runs the handler in
the request. The response is `204`, or the acknowledgement the handler returned.

## Install the packages

Queueing needs three Symfony components:

```bash
composer require symfony/webhook symfony/remote-event symfony/messenger
```

The bundle turns on its webhook services when these are installed and you use payum/core 2.0 or newer.
Make sure `framework.remote_event` is on. It is on by default when your application requires
`symfony/remote-event`, and you can set it explicitly:

```yaml
# config/packages/framework.yaml
framework:
    remote_event: true
```

Messenger needs a bus, which `framework.messenger` gives you. With the packages installed but no
`framework.remote_event`, the bundle does not use Messenger: it handles the webhook in the request, as if
the packages were missing.

## Handle webhooks on a queue

Without routing, Messenger handles the message in the request, before the response. To queue it, route
`ConsumeRemoteEventMessage` to a transport:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
        routing:
            Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage: async
```

Then run a worker:

```bash
bin/console messenger:consume async
```

The controller verifies the message while the PSP waits, so a forged webhook never reaches your queue.
It answers with the acknowledgement of the verified message. The acknowledgement the handler returns
when it runs later has nobody to read it, so it is not used on this path.

A message that fails in the worker follows your Messenger retry and failure transport settings. A
webhook whose token has since been deleted is not retried.

## Store the message first

Some PSPs resend a webhook for days when you answer late and some never resend. To keep a message that
you accepted, use a transport that stores it before the response, such as the Doctrine transport:

```yaml
framework:
    messenger:
        transports:
            async: 'doctrine://default'
        routing:
            Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage: async
```

The message is saved to the database before the `2xx` goes back, so a crashed worker loses nothing and
you can replay it from the failure transport.

## Change what happens to a webhook

The bundle registers one consumer, `Payum\Bundle\PayumBundle\Webhook\PayumWebhookConsumer`, as the
`payum` remote event consumer (`remote_event.consumer` tag, `consumer: payum`). It runs the gateway's
notify handler with the verified message.

To do something else, replace it by registering your own service with the same tag. To do something in
addition, decorate it:

```php
<?php

namespace App\Webhook;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsDecorator('payum.webhook.consumer')]
final class AuditedWebhookConsumer implements ConsumerInterface
{
    public function __construct(
        #[AutowireDecorated] private readonly ConsumerInterface $inner,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function consume(RemoteEvent $event): void
    {
        $this->logger->info('Payum webhook {id}', ['id' => $event->getId()]);

        $this->inner->consume($event);
    }
}
```

The event is a `Payum\Bundle\PayumBundle\Webhook\PayumRemoteEvent`. It carries `gatewayName`, the
verified `webhook` and the `tokenHash` of the notify token, or null on the unsafe route.

## Log rejected webhooks

A webhook that fails verification gets `400` and nothing else. To see them, listen to
`Payum\Core\Event\WebhookRejected`:

```php
<?php

namespace App\EventListener;

use Payum\Core\Event\WebhookRejected;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class LogRejectedWebhook
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(WebhookRejected $event): void
    {
        $this->logger->warning('Webhook for {gateway} rejected: {reason}', [
            'gateway' => $event->gatewayName,
            'reason' => $event->exception->getMessage(),
        ]);
    }
}
```

See [Events](../events.md) for the other events.
