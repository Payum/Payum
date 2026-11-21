# Subscriptions

An application runs a subscription in one of two modes, as
[Charge a customer on a schedule](../subscriptions.md) shows. When your application schedules the charges,
your gateway needs nothing new: each renewal is a capture on a stored payment method, which
[Stored payment methods](stored-payment-methods.md) covers. When the PSP schedules them, your gateway
creates, cancels and reads the subscription at the PSP, and reports each charge the PSP makes. This page
shows the handlers that do it.

The Offline gateway in `src/Payum/Offline` implements the three subscription handlers without a PSP. Read
them next to this page.

## Declare the capabilities

Three capabilities come from handlers, and one you declare:

| Capability | Where it comes from | Means |
| :--- | :--- | :--- |
| `CreateSubscription` | A `CreateSubscriptionHandlerInterface` handler | The PSP creates and charges a subscription. |
| `CancelSubscription` | A `CancelSubscriptionHandlerInterface` handler | The gateway cancels a subscription at the PSP. |
| `SyncSubscription` | A `SyncSubscriptionHandlerInterface` handler | The gateway reads a subscription's state from the PSP. |
| `CancelAtPeriodEnd` | Declared | The PSP can cancel at the end of the paid period. |

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Acme\Payum\Handler\CancelSubscriptionHandler;
use Acme\Payum\Handler\CreateSubscriptionHandler;
use Acme\Payum\Handler\NotifyHandler;
use Acme\Payum\Handler\SyncSubscriptionHandler;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresCapabilities
{
    public function handlers(): array
    {
        return [
            CreateSubscriptionHandler::class,
            CancelSubscriptionHandler::class,
            SyncSubscriptionHandler::class,
            NotifyHandler::class,
        ];
    }

    public function capabilities(): array
    {
        return [Capability::CancelAtPeriodEnd];
    }

    // name(), logo(), websiteUrl() and configClass() as in Defining a gateway
}
```

Payum refuses a cancel at the period end on a gateway that does not declare `CancelAtPeriodEnd`, with an
`UnsupportedOperationException`, before your handler runs. It also refuses to create a subscription the
application schedules, with a `LogicException`, so your create handler only ever sees subscriptions the
PSP schedules.

## Create the subscription

A `CreateSubscriptionHandlerInterface` handler creates the subscription at the PSP. It runs like a
capture: when the customer has to approve the subscription at the PSP, return `pending()` with a redirect,
and the same command runs again when the PSP sends the customer back:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
use DateTimeImmutable;
use Money\Currency;
use Money\Money;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\CreateSubscriptionHandlerInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Renewal;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;

final class CreateSubscriptionHandler implements CreateSubscriptionHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
    ) {
    }

    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $subscription = $context->subscription();
        $state = $context->state();

        if (! isset($state['subscription_id'])) {
            $request = [
                'customer' => $context->customer()?->getGatewayCustomerId((string) $context->gatewayName()),
                'return_url' => $context->token()?->getTargetUrl(),
                'metadata' => ['payum_reference' => $context->merchantReference()],
            ];

            $money = $subscription?->getMoney();
            $interval = $subscription?->getInterval();

            if (null !== $subscription?->getPlanReference()) {
                $request['plan'] = $subscription->getPlanReference();
            } elseif (null !== $money && null !== $interval) {
                $request['amount'] = (int) $money->getAmount();
                $request['currency'] = $money->getCurrency()->getCode();
                $request['interval'] = $interval->unit->value;
                $request['interval_count'] = $interval->count;
            } else {
                return SubscriptionResult::failed(new Failure(FailureReason::Configuration, message: 'Give the subscription a plan reference, or an amount and an interval.'));
            }

            $created = $this->api->createSubscription($request);
            $state['subscription_id'] = $created['id'];

            if (null !== $created['approve_url']) {
                return SubscriptionResult::pending(new Redirect($created['approve_url']));
            }
        }

        $found = $this->api->findSubscription($state['subscription_id']);

        $result = SubscriptionResult::active(new SubscriptionDetails(
            status: SubscriptionStatus::Active,
            reference: $found['id'],
            currentPeriodEnd: new DateTimeImmutable('@'.$found['current_period_end']),
        ));

        $invoice = $found['latest_invoice'];

        if (null === $invoice) {
            return $result;
        }

        return $result->withRenewal(new Renewal(
            $invoice['id'],
            new Money($invoice['amount'], new Currency($invoice['currency'])),
            PaymentStatus::Captured,
        ));
    }
}
```

`AcmeApi` stands for your PSP client. `$context->subscription()` is the subscription the application
created, and `$context->state()` is its details, so the PSP's id survives the redirect. Use the plan
reference when the application set one, and the amount and interval otherwise. When neither is there,
return a `Configuration` failure. Pass `getStartAt()` and `getCycles()` to PSPs that take a delayed start
or a number of charges.

Send `$context->merchantReference()` in the PSP's metadata, as for a payment. A webhook that arrives before
the create returns finds the subscription by it.

`SubscriptionDetails` is what the PSP reports. Payum writes it onto the subscription, saves it, records the
reference so later webhooks find the subscription, and dispatches `SubscriptionStatusChanged` when the
status moved. A null field leaves the subscription's value as it is.

When the PSP charges the customer while creating the subscription, return that charge with
`withRenewal()`. Payum records it as the subscription's first payment and marks its events `isFirst`.
When the PSP also saved the customer's payment method, add it with `withStoredPaymentMethod()`, as a
capture does.

The result has four forms:

| Result | Use it when |
| :--- | :--- |
| `SubscriptionResult::active($details)` | The PSP created the subscription. |
| `SubscriptionResult::pending($next)` | The customer has to approve it first. |
| `SubscriptionResult::updated($details)` | A cancel or a sync read the subscription's new state. |
| `SubscriptionResult::failed($failure)` | The PSP refused. The subscription is left as it was. |

A subscription result never sets a payment status: the subscription's own status is in its details.

## Cancel and sync

A `CancelSubscriptionHandlerInterface` handler cancels at the PSP. `$command->atPeriodEnd` asks for the
cancellation at the end of the paid period:

```php
public function handle(CancelSubscriptionCommand $command, Context $context): SubscriptionResult
{
    $subscription = $command->subscription();

    $found = $this->api->cancelSubscription((string) $subscription->getReference(), atPeriodEnd: $command->atPeriodEnd);

    return SubscriptionResult::updated(new SubscriptionDetails(
        status: $command->atPeriodEnd ? null : SubscriptionStatus::Canceled,
        cancelAt: $command->atPeriodEnd ? $subscription->getCurrentPeriodEnd() : null,
        canceledAt: $command->atPeriodEnd ? null : new DateTimeImmutable('@'.$found['canceled_at']),
    ));
}
```

A cancel at the period end leaves the status as it is and records `cancelAt`. The PSP reports `Canceled`
by webhook when the period ends. A `SyncSubscriptionHandlerInterface` handler reads the subscription from
the PSP and returns `SubscriptionResult::updated()` with what it found.

## Report renewals by webhook

The PSP tells you about each charge with a webhook. Return the PSP's id for the subscription from
`verify()`, and Payum passes the subscription to `handle()` as `$context->subscription()`:

```php
return WebhookEvent::verified(
    $event,
    $event['id'],
    $event['type'],
    reference: $event['data']['subscription'],
    merchantReference: $event['data']['metadata']['payum_reference'] ?? null,
);
```

Report the charge with `withRenewal()`, and the subscription's new state with `withSubscription()`:

```php
public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
{
    if (null === $context->subscription() || ! in_array($event->type, ['invoice.paid', 'invoice.payment_failed'], true)) {
        return NotifyResult::ignored();
    }

    $invoice = $event->payload['data'];
    $paid = 'invoice.paid' === $event->type;

    return NotifyResult::handled()
        ->withSubscription(new SubscriptionDetails(
            status: $paid ? SubscriptionStatus::Active : SubscriptionStatus::PastDue,
            currentPeriodEnd: new DateTimeImmutable('@'.$invoice['period_end']),
        ))
        ->withRenewal(new Renewal(
            $invoice['id'],
            new Money($invoice['amount'], new Currency($invoice['currency'])),
            $paid ? PaymentStatus::Captured : PaymentStatus::Failed,
            failure: $paid ? null : new Failure(FailureReason::Declined, $invoice['decline_code']),
            isFirst: 'subscription_create' === $invoice['billing_reason'],
        ));
}
```

`Payum\Core\Result\Renewal` is one charge: the PSP's id for it, the amount, the payment status, and
optionally a failure and the end of the period it pays for. For a new id, Payum creates a payment linked
to the subscription. For an id it has seen before, it updates that payment, so `invoice.paid` after an
earlier `invoice.payment_failed` turns the same payment `Captured`. Either way it dispatches
`StatusChanged`, `SubscriptionPaymentSucceeded` or `SubscriptionPaymentFailed`.

{% hint style="warning" %}
Return `PaymentStatus::Failed` for a declined charge. Payum dispatches `SubscriptionPaymentFailed` only
when the payment reaches `Failed`, so an application that chases failed payments never hears about a
decline you report with another status.
{% endhint %}

Set `isFirst` from the PSP's own marker for the subscription's first charge, as the sample does with
Stripe's `billing_reason` of `subscription_create`. The first charge's webhook can arrive before the call
that creates the subscription returns, and Payum cannot tell it from a later charge then. A renewal
returned from the create handler is always first.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
