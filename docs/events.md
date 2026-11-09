# Events

Payum announces what happens to a payment as [PSR-14](https://www.php-fig.org/psr/psr-14/) events. Listen
to them to send a receipt when a payment is captured, keep an audit trail, alert someone when a payment
provider is down, or send webhooks of your own. You do not have to change a gateway to do any of this.

## Register a dispatcher

Payum sends its events to the `Psr\EventDispatcher\EventDispatcherInterface` service. Until you register
one, it uses `Payum\Core\Event\NullEventDispatcher`, which drops every event. Register your dispatcher as
a global service:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(EventDispatcherInterface::class, $dispatcher)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Any PSR-14 dispatcher works. `symfony/event-dispatcher` is one, as the sample shows. You register it
once and it receives the events of every gateway.

## Listen to an event

Add a listener for the event class you care about. This one sends a receipt when a payment is captured:

```php
<?php

use Payum\Core\Event\StatusChanged;
use Payum\Core\Result\PaymentStatus;

$dispatcher->addListener(StatusChanged::class, function (StatusChanged $event) use ($mailer): void {
    if (PaymentStatus::Captured === $event->to) {
        $mailer->sendReceipt($event->subject);
    }
});
```

Every event has two public properties: `$event->command`, the command that caused it, and
`$event->context`, the [handler context](gateways/handlers.md) the command ran in. Read the payment,
token and gateway name from the context with `$event->context->subject()`, `$event->context->token()`
and `$event->context->gatewayName()`.

In a `CommandDispatched` listener, do not read the payment. Payum has not locked it yet, so reading it
loads a copy that a request running at the same time may be about to change. See
[Concurrent requests and idempotency](concurrency-and-idempotency.md).

A listener can only watch. Payum ignores what it returns, and it cannot change the result. If you need
to change what a command does, write a [middleware](middleware.md) instead. A listener that
throws an exception stops the command and the exception reaches your code, so keep slow or fragile work
out of the listener: put a job on a queue and let it fail there.

## The events

All events are in the `Payum\Core\Event` namespace:

| Event | Dispatched when | Extra properties |
| :--- | :--- | :--- |
| `CommandDispatched` | A command is about to run, before any middleware and before Payum locks the payment | |
| `HandlerResolved` | Payum has the handler that will run the command | `$handler` |
| `WebhookReceived` | A payment provider's notification passed verification, before the gateway acts on it | `$webhook` |
| `StatusChanged` | The payment moved from one status to another | `$subject`, `$from`, `$to` |
| `ResultReturned` | The command finished and returned a result | `$result` |
| `FailureRaised` | The result is a failure, such as a declined card | `$failure`, `$result` |
| `CommandFailed` | The command threw an exception | `$exception` |

A capture that succeeds dispatches `CommandDispatched`, `HandlerResolved`, `StatusChanged` and
`ResultReturned`, in that order.

Every event extends `Payum\Core\Event\Event`, so one listener can type-hint the base class to handle
several events. You still register it once for each event class. The Symfony dispatcher, for example,
looks up listeners by the class of the event, so a listener registered for `Event::class` never runs.

### `StatusChanged` fires only when the status changes

If a captured payment is captured again, because the provider sent the same webhook twice or the customer
reloaded the return page, `StatusChanged` does not fire. A listener that ships an order on `StatusChanged`
therefore ships it once.

Payum only tracks a status for a payment model that implements `Payum\Core\Model\StatusAwareInterface`.
A model that does not implement it never fires `StatusChanged`. See [Payment status](payment-status.md).

### `FailureRaised` and `CommandFailed`

The two events cover different problems:

* `FailureRaised` is a decline: insufficient funds, an expired card, or a customer who abandoned 3-D
  Secure. The provider answered, and the answer was no. It follows the `ResultReturned` for the same
  result. Show the customer a message and let them try again.
* `CommandFailed` is a fault: the provider could not be reached, the credentials are wrong, or a webhook
  signature did not match. There is no result. Payum rethrows the exception right after the event, so
  your listener sees it before your code does. This is the one to alert on.

### `WebhookReceived` fires only for verified messages

A notification that fails the gateway's check never reaches `WebhookReceived`. The gateway throws
`Payum\Core\Exception\WebhookNotVerifiedException` and you get `CommandFailed` instead.

Check `$event->webhook->isVerified()` all the same. Some providers do not sign their notifications, and
the gateway then marks them as unverified. Do not trust what an unverified message says about a payment.
See [Payment notifications](instant-payment-notification.md).

## Examples

Write one audit line for every command, whatever the gateway:

```php
<?php

use Payum\Core\Event\CommandDispatched;
use Payum\Core\Event\CommandFailed;
use Payum\Core\Event\Event;
use Payum\Core\Event\ResultReturned;

$audit = function (Event $event) use ($logger): void {
    $logger->info('{event} {gateway} {command}', [
        'event' => $event::class,
        'gateway' => $event->context->gatewayName(),
        'command' => $event->command::class,
    ]);
};

foreach ([CommandDispatched::class, ResultReturned::class, CommandFailed::class] as $eventClass) {
    $dispatcher->addListener($eventClass, $audit);
}
```

Spot a webhook the provider sent twice:

```php
<?php

use Payum\Core\Event\WebhookReceived;

$dispatcher->addListener(WebhookReceived::class, function (WebhookReceived $event) use ($seen, $logger): void {
    if (null !== $event->webhook->id && $seen->has($event->webhook->id)) {
        $logger->info('Webhook {id} delivered again', ['id' => $event->webhook->id]);
    }
});
```

`$seen` stands for your own record of webhook ids you have already handled.

Report faults to your error tracker, and nothing else:

```php
<?php

use Payum\Core\Event\CommandFailed;

$dispatcher->addListener(CommandFailed::class, function (CommandFailed $event) use ($errorTracker): void {
    $errorTracker->captureException($event->exception, [
        'gateway' => $event->context->gatewayName(),
        'command' => $event->command::class,
    ]);
});
```

## Use your framework's dispatcher

Register the dispatcher your application already uses, so Payum's events reach your existing listeners.

In Symfony, the `event_dispatcher` service implements PSR-14. Inject it and register it as it is:

```php
->addGlobalService(EventDispatcherInterface::class, $eventDispatcher)
```

Laravel's `Illuminate\Events\Dispatcher` does not implement PSR-14, so wrap it in a small adapter:

```php
<?php

namespace App\Payments;

use Illuminate\Contracts\Events\Dispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;

final class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function __construct(private readonly Dispatcher $events)
    {
    }

    public function dispatch(object $event): object
    {
        $this->events->dispatch($event);

        return $event;
    }
}
```

Then register the adapter:

```php
->addGlobalService(EventDispatcherInterface::class, new LaravelEventDispatcher($app['events']))
```

See [Integrate with a framework](di/framework-integration.md) for the rest of the framework wiring.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
