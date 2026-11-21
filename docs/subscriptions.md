# Charge a customer on a schedule

A subscription charges the same customer again and again, such as a monthly fee. Payum supports two ways
to run one. Either the payment provider (PSP) keeps the schedule and charges the customer itself, or your
application keeps the schedule and charges a payment method the customer saved. Payum records each charge
as a payment linked to the subscription and tells your listeners about it, the same way in both modes.

This page uses the Offline gateway, which runs both modes without talking to any PSP, so you can try every
step before you have an account. The Offline gateway gives a new subscription its first period, from the
start date or from today, but never renews it by itself.

## Choose who schedules the charges

Ask the gateway what it supports:

```php
<?php

use Payum\Core\Gateway\Capability;
use Payum\Core\Model\SubscriptionSchedule;

$capabilities = $payum->getGateway('offline')->capabilities();

$schedule = in_array(Capability::CreateSubscription, $capabilities, true)
    ? SubscriptionSchedule::Gateway
    : SubscriptionSchedule::Application;
```

| Capability | What the gateway can do |
| :--- | :--- |
| `CreateSubscription` | Create a subscription that the PSP charges on its own schedule. |
| `CancelSubscription` | Cancel a PSP subscription. |
| `SyncSubscription` | Ask the PSP for the current state of a subscription. |
| `CancelAtPeriodEnd` | Cancel a PSP subscription at the end of the period the customer paid for. |
| `StoredPaymentMethods` and `Recurring` | Save a payment method and charge it with no customer present, which is all your application needs to schedule the charges itself. |

Let the PSP schedule the charges when it can. The charges keep running when your cron job is down, and
most PSPs retry a failed card on their own. Schedule them yourself when the PSP has no subscriptions, or
when you need billing rules the PSP does not offer.

## Set up

Register storages for subscriptions, customers and stored payment methods next to your payments, and an
event dispatcher to hear about each charge. `addDefaultStorages()` registers file storages for all of
them, which is enough to try this page:

```php
<?php
// config.php

use Payum\Core\PayumBuilder;
use Payum\Offline\OfflineConfig;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

require __DIR__.'/vendor/autoload.php';

$dispatcher = new EventDispatcher();

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(EventDispatcherInterface::class, $dispatcher)
    ->registerGateway('offline', new OfflineConfig())
    ->getPayum();
```

The samples load the customer `customer-42`, which [The customer](stored-payment-methods.md#the-customer)
creates. For real payments, store subscriptions in your database next to your payments.
[Storages](storages.md#store-subscriptions) shows the Doctrine entities, including the links between a
payment and its subscription, which you map yourself.

{% hint style="warning" %}
In production, give Payum a reference index that lasts between requests, as
[Payment notifications](instant-payment-notification.md#receive-webhooks-on-a-route-you-own) shows. With the
default in-memory index, a renewal webhook that the PSP sends again in a later request records a second
payment for the same charge.
{% endhint %}

## Let the gateway schedule the charges

### Create the subscription

Create a `Payum\Core\Model\Subscription` for the customer, save it, and send the customer to a
`CreateSubscription` token:

```php
<?php
// prepare-subscription.php

use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Security\TokenPurpose;

require __DIR__.'/config.php';

$subscription = new Subscription();
$subscription->setGatewayName('offline');
$subscription->setSchedule(SubscriptionSchedule::Gateway);
$subscription->setCustomer($payum->getStorage(Customer::class)->find('customer-42'));
$subscription->setTotalAmount(1000);
$subscription->setCurrencyCode('EUR');
$subscription->setInterval(BillingInterval::fromString('1 month'));
$subscription->setDescription('Pro plan');
$payum->getStorage(Subscription::class)->update($subscription);

$token = $payum->getTokenIssuer()->create(TokenPurpose::CreateSubscription, 'offline', $subscription, 'subscribed.php');

header('Location: '.$token->getTargetUrl());
```

`BillingInterval::fromString()` takes a count and a unit: `day`, `week`, `month` or `year`, such as
`'2 week'`. Some PSPs, such as PayPal, charge only for a plan you set up in their dashboard. Pass its id
with `setPlanReference()`, and the gateway uses the plan in place of the amount and interval. Use
`setStartAt()` to delay the first charge, for a free trial, and `setCycles()` to stop after a number of
charges.

The token's target is `subscribe.php`, which runs the gateway:

```php
<?php
// subscribe.php

require __DIR__.'/config.php';

$payum->subscribe()->send();
```

The PSP may send the customer to its own page to approve the subscription, and back to this script. When
the subscription is set up, the customer lands on `subscribed.php`:

```php
<?php
// subscribed.php

use Payum\Core\Result\SubscriptionStatus;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify(Request::createFromGlobals());
$payum->getHttpRequestVerifier()->invalidate($token);

$identity = $token->getDetails();
$subscription = $payum->getStorage($identity->getClass())->find($identity);

if (SubscriptionStatus::Active === $subscription->getStatus()) {
    echo 'You are subscribed.';
}
```

Payum writes what the PSP reports onto the subscription and saves it: the PSP's id for it, its status
and the end of the current period. When the customer does not have to approve anything, create the subscription
from your own code:

```php
use Payum\Core\Command\CreateSubscriptionCommand;

$result = $payum->getGateway('offline')->execute(CreateSubscriptionCommand::forSubscription($subscription));
```

### Receive the renewals

The PSP charges the customer at each period and tells you with a webhook (a message the PSP sends to your
server). Receive webhooks as [Payment notifications](instant-payment-notification.md) describes. For each
charge, Payum creates a `Payum\Core\Model\Payment` linked to the subscription, records its status, saves
it and dispatches the events in [Know about each charge](#know-about-each-charge). A webhook for a charge
Payum already recorded updates that payment.

When your payments are your own class, tell Payum to create renewals of that class. It needs a storage:

```php
use Acme\Entity\Payment;

$builder->setRenewalPaymentClass(Payment::class);
```

To create an order for each renewal, or fill in fields of your own, pass your own
`Payum\Core\Subscription\RenewalPaymentFactoryInterface` to `setRenewalPaymentFactory()`. Its `create()`
gets the subscription and the `Payum\Core\Result\Renewal` the PSP reported, and returns an unsaved payment.

{% hint style="info" %}
A framework integration must call these two setters on the builder. A service defined only in the
container you pass to `setGlobalContainer()` does not replace the gateway's default.
{% endhint %}

### Cancel the subscription

Cancel it now, or at the end of the period the customer paid for:

```php
<?php

use Payum\Core\Command\CancelSubscriptionCommand;

$gateway = $payum->getGateway($subscription->getGatewayName());

$gateway->execute(CancelSubscriptionCommand::forSubscription($subscription));

$gateway->execute(CancelSubscriptionCommand::forSubscription($subscription, atPeriodEnd: true));
```

A cancel now sets the status to `Canceled` and records `getCanceledAt()`. A cancel at the period end keeps
the subscription `Active` and records the end of the current period in `getCancelAt()`. The PSP reports
`Canceled` by webhook when that date comes. A subscription without an interval has no period end, so a
cancel at the period end on the Offline gateway then records no date.

Cancelling at the period end needs `Capability::CancelAtPeriodEnd`. On a gateway without it, Payum throws
`Payum\Core\Exception\UnsupportedOperationException` and sends nothing to the PSP. Keep the customer's
access until `getCurrentPeriodEnd()` yourself, then cancel now.

### Read the state from the PSP

To refresh a subscription from the PSP, for example from an admin page:

```php
use Payum\Core\Command\SyncSubscriptionCommand;

$payum->getGateway($subscription->getGatewayName())->execute(SyncSubscriptionCommand::forSubscription($subscription));
```

Payum records what the PSP reports and saves the subscription. The status is a
`Payum\Core\Result\SubscriptionStatus`:

| Status | Means |
| :--- | :--- |
| `Pending` | Not set up yet, or the customer has not approved it. |
| `Trialing` | In a free trial. No charge yet. |
| `Active` | The customer is paying. |
| `PastDue` | A charge failed, and the PSP is retrying it. |
| `Paused` | The PSP stopped charging for now. |
| `Canceled` | Cancelled. No more charges. |
| `Expired` | Ended after its number of charges. |
| `Unknown` | The PSP reported a status Payum does not recognise. |

## Let your application schedule the charges

### Take the first payment

Create a subscription with `SubscriptionSchedule::Application` and a new stored payment method, then take
the first payment through a token with both linked to it. The customer is there to approve it, and the
gateway saves the method during the payment:

```php
<?php
// prepare.php

use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;

require __DIR__.'/config.php';

$customer = $payum->getStorage(Customer::class)->find('customer-42');

$method = new StoredPaymentMethod();
$method->setGatewayName('offline');
$method->setCustomer($customer);
$payum->getStorage(StoredPaymentMethod::class)->update($method);

$subscription = new Subscription();
$subscription->setGatewayName('offline');
$subscription->setSchedule(SubscriptionSchedule::Application);
$subscription->setCustomer($customer);
$subscription->setStoredPaymentMethod($method);
$subscription->setTotalAmount(1000);
$subscription->setCurrencyCode('EUR');
$subscription->setInterval(BillingInterval::fromString('1 month'));
$payum->getStorage(Subscription::class)->update($subscription);

$payment = new Payment();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(1000);
$payment->setCustomer($customer);
$payment->setStoredPaymentMethod($method);
$payment->setSubscription($subscription);

$captureToken = $payum->prepare('offline', $payment);

header('Location: '.$captureToken->getTargetUrl());
```

`capture.php` and `done.php` stay as they are in [Get started](get-it-started.md). Do not execute a
`CreateSubscriptionCommand` for this subscription: Payum throws a `LogicException`, because the gateway
does not schedule it.

### Charge each renewal

When a period is due, create the next payment from your cron job, save it and capture it. Run this for each
subscription that is due:

```php
<?php
// renew.php

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Subscription;
use Payum\Core\Result\StoredPaymentMethodStatus;

require __DIR__.'/config.php';

$subscription = $payum->getStorage(Subscription::class)->find($subscriptionId);
$method = $subscription->getStoredPaymentMethod();

if (StoredPaymentMethodStatus::Active !== $method?->getStatus()) {
    // ask the customer for a new payment method
    return;
}

$payment = new Payment();
$payment->setNumber(uniqid());
$payment->setCurrencyCode($subscription->getCurrencyCode());
$payment->setTotalAmount($subscription->getTotalAmount());
$payment->setCustomer($subscription->getCustomer());
$payment->setStoredPaymentMethod($method);
$payment->setSubscription($subscription);
$payum->getStorage(Payment::class)->update($payment);

$result = $payum->getGateway($subscription->getGatewayName())->execute(CaptureCommand::forPayment($payment));

$payum->getStorage(Payment::class)->update($payment);
```

The command has no token, so the gateway tells the PSP that you started the charge, as
[Charge a saved method](stored-payment-methods.md#charge-a-saved-method) explains. Payum dispatches the
same events as in gateway mode, because the payment is linked to the subscription.

In this mode Payum never changes the subscription's status. You decide when a charge was due and what a
failed one means, so set the status yourself, for example from the listener in the next section.

### When the bank wants the customer back

Some banks refuse a charge with no customer present until the customer confirms it, as European cards
under Strong Customer Authentication do. The result then carries `FailureReason::AuthenticationRequired`.
Send the customer a link to pay that payment themselves:

```php
use Payum\Core\Result\FailureReason;

if (FailureReason::AuthenticationRequired === $result->failure?->reason) {
    $token = $payum->prepare($subscription->getGatewayName(), $payment, 'done.php');

    $mailer->askToConfirmPayment($subscription->getCustomer(), $token->getTargetUrl());
}
```

The customer pays through `capture.php` like the first payment.

## Know about each charge

Payum dispatches `Payum\Core\Event\SubscriptionPaymentSucceeded` when a payment of a subscription is
captured or authorized, and `Payum\Core\Event\SubscriptionPaymentFailed` when one fails. Both carry the
subscription, the payment and `isFirst`, which is true for the charge that started the subscription: in
application mode, the first charge on the stored method. This
listener extends an application-scheduled subscription by one period after each charge:

```php
<?php

use Payum\Core\Event\SubscriptionPaymentFailed;
use Payum\Core\Event\SubscriptionPaymentSucceeded;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Result\SubscriptionStatus;

$dispatcher->addListener(SubscriptionPaymentSucceeded::class, function (SubscriptionPaymentSucceeded $event) use ($payum, $mailer): void {
    $subscription = $event->subscription;

    if ($subscription instanceof Subscription && SubscriptionSchedule::Application === $subscription->getSchedule()) {
        $interval = $subscription->getInterval();
        $periodStart = $subscription->getCurrentPeriodEnd() ?? new DateTimeImmutable();
        $subscription->setStatus(SubscriptionStatus::Active);
        $subscription->setCurrentPeriodEnd(null === $interval ? null : $periodStart->modify('+'.$interval->toString()));
        $payum->getStorage(Subscription::class)->update($subscription);
    }

    if ($event->isFirst) {
        $mailer->sendWelcome($subscription->getCustomer());
    }
});

$dispatcher->addListener(SubscriptionPaymentFailed::class, function (SubscriptionPaymentFailed $event) use ($payum, $mailer): void {
    $subscription = $event->subscription;

    if ($subscription instanceof Subscription && SubscriptionSchedule::Application === $subscription->getSchedule()) {
        $subscription->setStatus(SubscriptionStatus::PastDue);
        $payum->getStorage(Subscription::class)->update($subscription);
    }

    $mailer->sendPaymentFailed($subscription->getCustomer(), $event->failure?->reason);
});
```

Leave the status alone for a gateway-scheduled subscription: Payum records what the PSP reports. Keep in
mind:

- In application mode, `isFirst` means the first charge on the stored method. A subscription started with
  a card the customer saved earlier has no first charge, and a payment with no stored method is never
  first. Read `isFirst` from the event. The stored method looks the same as for any later charge once the
  payment is done.
- `SubscriptionPaymentFailed` fires only when the payment reaches `Failed`. A gateway that leaves a declined
  charge at another status sends no event.
- A charge that is authorized and later captured fires `SubscriptionPaymentSucceeded` once, at the
  authorization. If the capture then fails, you get `SubscriptionPaymentFailed` for the same payment.
- A webhook that the PSP sends twice fires nothing the second time.

`StatusChanged` still fires for each renewal payment, so a listener that fulfils orders on it sees renewals
too. [Events](events.md) lists every event.

### When the subscription changes

Payum dispatches `Payum\Core\Event\SubscriptionStatusChanged` each time it records a new status on a
subscription, such as `Active` to `PastDue` or `Canceled`. Use it to give or take away access:

```php
use Payum\Core\Event\SubscriptionStatusChanged;
use Payum\Core\Result\SubscriptionStatus;

$dispatcher->addListener(SubscriptionStatusChanged::class, function (SubscriptionStatusChanged $event) use ($access): void {
    if (SubscriptionStatus::Canceled === $event->to || SubscriptionStatus::Expired === $event->to) {
        $access->revoke($event->subscription->getCustomer());
    }
});
```

Payum dispatches it only for a status it records. In application mode you set the status yourself, so
it does not fire.

## What Payum leaves to you

Payum moves the money and records what happened. Your application owns the billing rules:

- **Scheduling.** In application mode, your cron job decides when a charge is due.
- **Plans.** Payum has no plan or price catalogue. Keep your plans and copy the amount and interval onto
  each subscription.
- **Trials.** `setStartAt()` asks a PSP to delay the first charge. In application mode, take the first
  payment when the trial ends, or save the method without a payment as
  [Save a method without taking a payment](stored-payment-methods.md#save-a-method-without-taking-a-payment)
  shows.
- **Dunning.** Payum does not retry a failed charge or email the customer. A PSP that schedules the
  charges may retry on its own.
- **Proration.** Payum does not work out partial amounts when a customer changes plan.
- **Pause.** Payum has no command to pause or resume a subscription.

## Advanced: write a gateway that runs subscriptions

A gateway supports subscriptions with three handlers and a webhook handler that reports each renewal.
[Subscriptions in a gateway](gateways/subscriptions.md) covers the handlers, the results and the renewal
webhooks.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
