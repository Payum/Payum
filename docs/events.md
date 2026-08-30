# Events

Payum announces what happens to a payment as [PSR-14](https://www.php-fig.org/psr/psr-14/) events. Listen
to them to send a receipt, write an audit trail, fan out your own webhooks, or update a dashboard —
without touching a gateway.

By default the events go nowhere: the container holds a `Payum\Core\Event\NullEventDispatcher` until you
register a real one.

```php
<?php

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(EventDispatcherInterface::class, $dispatcher)
    ->registerGateway('acme', new AcmeConfig('sk_…'))
    ->getPayum();
```

Any PSR-14 implementation will do. `symfony/event-dispatcher` and `league/event` both are one already;
for a framework whose dispatcher is not, there is a small adapter under [Frameworks](#frameworks).

One dispatcher is registered once and hears about every gateway.

## Listening

```php
<?php

use Payum\Core\Event\StatusChanged;
use Payum\Core\Result\PaymentStatus;

$dispatcher->addListener(StatusChanged::class, function (StatusChanged $event) use ($mailer) {
    if (PaymentStatus::Captured === $event->to) {
        $mailer->sendReceipt($event->subject);
    }
});
```

Every event carries the command that caused it and the [`Context`](gateways/handlers.md) it ran in, so
`$event->context->subject()`, `$event->context->token()` and `$event->context->gatewayName()` are always
available.

A listener is a bystander. Whatever it returns is ignored, and it cannot change the result — that is what
[middleware](gateways/middleware.md) is for. It *can* stop a command by throwing, but only by taking the
whole command down with it, so keep the risky part of the work out of the listener: put a job on a queue
and let it fail there.

## What is dispatched

| Event | When | Also carries |
| :--- | :--- | :--- |
| `CommandDispatched` | A command is about to run, before any middleware | |
| `HandlerResolved` | The container produced the handler that will answer it | `$handler` |
| `WebhookReceived` | A PSP's message passed verification, before it is acted on | `$webhook` |
| `StatusChanged` | The payment moved from one status to another | `$subject`, `$from`, `$to` |
| `ResultReturned` | The command finished | `$result` |
| `FailureRaised` | The PSP said no | `$failure`, `$result` |
| `CommandFailed` | An exception escaped the command | `$exception` |

All of them live in `Payum\Core\Event` and extend `Payum\Core\Event\Event`, so a listener that handles
several can type-hint the base. Registering against the base is a different question, and the answer is
your dispatcher's: Symfony's keys listeners on the concrete class of the event it is given, so there you
register the same callable once per event class.

A successful capture dispatches, in order:

```
CommandDispatched → HandlerResolved → StatusChanged → ResultReturned
```

Three of the entries above are worth a closer look.

**`StatusChanged` fires only on an actual move.** A payment already `Captured` that is captured again — a
redelivered webhook, a customer refreshing the return URL — says nothing, so a listener that fulfils an
order cannot fulfil it twice. Nor does a payment that tracks no status fire it at all: implement
`Payum\Core\Model\StatusAwareInterface` on your model to get these. `$from` is null when the payment had
no status recorded yet.

**`FailureRaised` and `CommandFailed` are not the same thing**, and the split matters:

* `FailureRaised` is a *decline* — an insufficient-funds card, an expired card, an authentication the
  customer abandoned. The command succeeded in the sense that it got an answer; the answer was no. It
  follows the `ResultReturned` carrying the same result. Show the customer something and let them retry.
* `CommandFailed` is a *fault* — an unreachable PSP, credentials that stopped working, a webhook whose
  signature did not check out. There is no result. The exception is rethrown right after, so your listener
  sees it a moment before the caller does. Page someone.

**`WebhookReceived` only fires for a message that verified.** A forged one throws out of `verify()` and
arrives as `CommandFailed` instead. Even so, check `$event->webhook->isVerified()`: a gateway whose PSP
signs nothing reports `WebhookEvent::unverified()`, and what such a message *says* is not to be trusted.

## Examples

Audit every command, whatever it is:

```php
<?php

use Payum\Core\Event\CommandDispatched;
use Payum\Core\Event\CommandFailed;
use Payum\Core\Event\Event;
use Payum\Core\Event\ResultReturned;

$audit = function (Event $event) use ($log) {
    $log->write($event::class, $event->context->gatewayName(), $event->command::class);
};

foreach ([CommandDispatched::class, ResultReturned::class, CommandFailed::class] as $eventClass) {
    $dispatcher->addListener($eventClass, $audit);
}
```

Notice a webhook you have already handled, before the handler runs again:

```php
<?php

use Payum\Core\Event\WebhookReceived;

$dispatcher->addListener(WebhookReceived::class, function (WebhookReceived $event) use ($seen, $log) {
    if (null !== $event->webhook->id && $seen->has($event->webhook->id)) {
        $log->info('Redelivery of {id}', ['id' => $event->webhook->id]);
    }
});
```

Alert on a fault, and only a fault:

```php
<?php

use Payum\Core\Event\CommandFailed;

$dispatcher->addListener(CommandFailed::class, function (CommandFailed $event) use ($sentry) {
    $sentry->captureException($event->exception, [
        'gateway' => $event->context->gatewayName(),
        'command' => $event->command::class,
    ]);
});
```

## Frameworks

Register the application's own dispatcher, so that Payum's events reach the listeners you already have.

Symfony — its `event_dispatcher` is PSR-14 as it stands, so inject it and register it:

```php
->addGlobalService(EventDispatcherInterface::class, $eventDispatcher)
```

Laravel — `Illuminate\Events\Dispatcher` is not, so wrap it:

```php
<?php

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

```php
->addGlobalService(EventDispatcherInterface::class, new LaravelEventDispatcher($app['events']))
```

## A caveat about older gateways

Events are dispatched for commands. A gateway that still ships 1.x actions rather than handlers — see
[Migrating a gateway from 1.x](gateways/migrating-from-v1.md) — announces nothing, because there is no
command to announce. `Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension` is what covers that
case, and it is deprecated along with the rest of the actions.

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the
support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
