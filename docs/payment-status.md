# Payment status

Your application needs to know where each payment stands: whether the money was taken, is still on its
way, or never arrived. Payum describes that with one set of statuses that is the same for every gateway,
and it can record the current status on your payment after every operation, so you can read it without
asking the provider.

## The statuses

A status is a case of the `Payum\Core\Result\PaymentStatus` enum:

| Status | Stored as | Means |
| :--- | :--- | :--- |
| `New` | `new` | Nothing has happened yet. A payment starts here. |
| `Pending` | `pending` | Started but not finished: the customer is still at the provider, or the provider has not settled. |
| `Authorized` | `authorized` | The amount is reserved on the customer's account but not taken. Capture it or cancel it. |
| `Captured` | `captured` | The money is taken. |
| `PartiallyCaptured` | `partially_captured` | Part of the amount is taken. |
| `Refunded` | `refunded` | You gave all the money back. |
| `PartiallyRefunded` | `partially_refunded` | You gave part of the money back. |
| `Canceled` | `canceled` | Called off before money moved: an authorization voided, or a payment abandoned. |
| `Failed` | `failed` | The payment did not go through and will not. |
| `Expired` | `expired` | The payment or the authorization ran out of time. |
| `Suspended` | `suspended` | The provider put the payment on hold. |
| `Disputed` | `disputed` | The cardholder opened a dispute. You still have the money, but you may lose it. |
| `ChargedBack` | `charged_back` | The dispute was lost and the money went back to the cardholder. Unlike `Refunded`, you did not choose this. |
| `PaidOut` | `payedout` | A payout reached its recipient. |
| `Unknown` | `unknown` | The gateway cannot tell what state the payment is in. |

The "Stored as" column is the enum's backing value, which is what ends up in your database. `PaidOut` is
stored as `payedout`, so use that spelling in a raw SQL query.

A payout goes through the same statuses as a payment, ending at `PaidOut` instead of `Captured`.

{% hint style="info" %}
Give every `match` over `PaymentStatus` a `default` arm, because the set can grow. A `match` without one
throws an `UnhandledMatchError` for a case it does not list.
{% endhint %}

## Record the status on your payment

Payum records a status only on a payment that has somewhere to keep it, which means its class
implements `Payum\Core\Model\StatusAwareInterface`. Payum's own `Payum\Core\Model\Payment` and
`Payum\Core\Model\Payout` do. A new one starts at `PaymentStatus::New`, and the Doctrine mapping Payum
ships stores the status in a `payum_status` column.

Your own order or payment class needs `getStatus()` and `setStatus()` to have its status recorded.
[Use your own payment model](your-order-integration.md) shows the class, and [Storages](storages.md)
shows how to map it with Doctrine.

## Read the status

Read the recorded status through `Payum\Core\Model\PaymentStatuses`:

```php
<?php

use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Result\PaymentStatus;

$status = PaymentStatuses::of($payment);   // ?PaymentStatus

if (PaymentStatus::Captured === $status) {
    // ship the order
}

PaymentStatuses::isTracked($payment);      // false when the class does not implement StatusAwareInterface
```

`PaymentStatuses::of()` returns `null` for a payment that does not implement `StatusAwareInterface`.
That is not the same as `New`. It means Payum does not know, because there was nowhere to record it.
Calling it instead of `$payment->getStatus()` keeps code working when it is handed a payment class that
does not track a status.

Right after an operation, the result carries the same answer:

```php
$result = $payum->getGateway('acme')->execute(SyncCommand::forPayment($payment));

$result->status;   // the status Payum just recorded, or null when the operation concluded nothing
```

## How Payum keeps it current

After every command, Payum writes the status from the result onto the payment. That covers every
operation on [Payment operations](payment-operations.md) and every notification the provider sends. A few
rules decide when the status changes:

- A result whose status is `null` leaves the payment alone. A refund the provider refused, for example,
  leaves a captured payment `Captured`.
- A refund, void or capture the provider accepted but has not finished also leaves the payment alone,
  so a captured payment stays `Captured` while its refund is in progress. `$result->isAccepted()` is
  true, and the notification that follows sets the final status.
- A decline usually leaves the status where it was, so a customer whose card was declined can try another
  card. The gateway moves the payment to `Failed` only when it will not go through.
- An exception leaves the status alone, because nobody learned what the payment's state is.
- When a gateway runs one command inside another, the outer command writes last, so the status is the
  answer to the operation you asked for.

Payum saves the payment for you when the command came in on a token, as it does in `capture.php` or
`notify.php`. When you execute a command built with `forPayment()`, Payum changes the status on the
object you passed, and you save it:

```php
$gateway->execute(SyncCommand::forPayment($payment));

$payum->getStorage($payment::class)->update($payment);
```

When a notification never arrives, the recorded status falls behind the provider. A `SyncCommand` asks
the provider and records the answer. See
[Ask the provider for the current state](payment-operations.md#ask-the-provider-for-the-current-state).

## React when the status changes

Each time a recorded status changes, Payum dispatches a `Payum\Core\Event\StatusChanged` event with the
payment and the old and new status:

```php
<?php

use Payum\Core\Event\StatusChanged;
use Payum\Core\Result\PaymentStatus;

$dispatcher->addListener(StatusChanged::class, function (StatusChanged $event): void {
    if (PaymentStatus::Captured === $event->to) {
        // $event->subject is the payment; $event->from is its previous status
    }
});
```

The event fires wherever the change came from: the customer's capture, a refund from your admin screen,
or a notification nobody was waiting for. It fires only when the status really moves, so a notification
the provider sends twice does not fulfil an order twice. A payment that does not implement
`StatusAwareInterface` never fires it. The event fires before Payum saves the payment, so a job you queue
from the listener should carry the new status rather than read it back from storage. [Events](events.md) shows how to register a dispatcher and lists
the other events.

## Find payments by status

The status is a column on your payment, so you can query it like any other. With Doctrine:

```php
<?php

use App\Entity\Payment;
use Payum\Core\Result\PaymentStatus;

$pending = $entityManager->getRepository(Payment::class)->findBy(['status' => PaymentStatus::Pending]);
```

A reconciliation job does this. It finds the payments still `Pending` and runs a `SyncCommand` for
each one.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
