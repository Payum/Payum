# Results

A handler answers with a result: the payment's status after the operation, what the customer must do
next, the PSP's transaction id, and why it failed if it did. Each command has its own result class,
which adds what only that operation has.

## What every result carries

```php
$result->status;         // ?PaymentStatus, null when the operation concluded nothing
$result->next;           // ?NextAction, what the customer must do, null when finished
$result->transactionId;  // ?string, the PSP's id for the operation
$result->failure;        // ?Failure, set when the PSP declined
$result->raw;            // array, the PSP's own response

$result->isSuccessful();        // finished and did not fail
$result->isFailed();            // has a failure, or the status is Failed
$result->requiresInteraction(); // $result->next is set
$result->isAccepted();          // the PSP took the operation on and reports the outcome later
```

| Result | Adds |
| :--- | :--- |
| `CaptureResult` | `capturedAmount`, `capturedMoney` |
| `AuthorizeResult` | `authorizedAmount`, `authorizedMoney`, `expiresAt` |
| `RefundResult` | `refundedAmount`, `refundedMoney` |
| `CancelResult` | |
| `PayoutResult` | `paidOutAmount`, `paidOutMoney` |
| `SyncResult` | |
| `NotifyResult` | `acknowledgement` |

All of them are in `Payum\Core\Result`.

## Build a result

Use the named constructors. Each one sets the status that goes with it:

```php
use Money\Money;
use Payum\Core\Result\AuthorizeResult;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Result\RefundResult;
use Payum\Core\Result\SyncResult;
use Payum\Core\Result\PaymentStatus;

CaptureResult::captured('ch_1');                              // the full amount was taken
CaptureResult::captured('ch_1', Money::EUR(1000));            // the same, saying how much
CaptureResult::partiallyCaptured('ch_1', Money::EUR(600));    // only part of it was taken
CaptureResult::pending(new Redirect('https://pay.acme.test/chk_1')); // the customer must act
CaptureResult::pending();                                     // waiting on the PSP, nothing to show
CaptureResult::accepted('cap_1');                             // capture of an authorization, outcome later
CaptureResult::canceled();
CaptureResult::failed(new Failure(FailureReason::Declined, 'card_declined'));

AuthorizeResult::authorized('auth_1', Money::EUR(1000), new DateTimeImmutable('+7 days'));

RefundResult::refunded('re_1', Money::EUR(1000));
RefundResult::partiallyRefunded('re_1', Money::EUR(300));
RefundResult::accepted('re_1');                               // outcome later, by webhook

CancelResult::canceled('chk_1');
CancelResult::accepted('void_1');                             // outcome later, by webhook
PayoutResult::paidOut('po_1', Money::EUR(5000));
SyncResult::synced(PaymentStatus::Captured, 'ch_1');
```

Every constructor also takes the PSP's response as `raw`, for an application that needs a field your
result does not expose.

An amount can be a `Money` or an int in minor units of the payment's currency. Pass a `Money` and both
properties are filled in, so an application that reads integers keeps working:

```php
$result->capturedMoney;    // ?Money, set when the handler passed one
$result->capturedAmount;   // ?int, minor units
```

`partiallyCaptured()` requires the amount, because a partial capture is meaningless without it. See
[Money and currencies](../money.md).

## Next actions

`$result->next` says what the customer must do before the operation can finish. It describes what to
do, not an HTTP response, so the application decides how to carry it out, and a JSON API can send it
to a mobile app as data.

| Next action | Carries | Means |
| :--- | :--- | :--- |
| `Redirect` | `url`, `statusCode`, `headers` | Send the customer to another URL |
| `PostRedirect` | `url`, `fields` | Send the customer to another URL by POST, usually as a form that submits itself |
| `RenderTemplate` | `template`, `context` | Show a page the gateway ships, such as a card form. See [Templates](templates.md) |
| `Challenge` | `url`, `parameters`, `version` | An extra authentication step, usually 3-D Secure |
| `Poll` | `retryAfterSeconds` | Nothing to show. The PSP has not finished, ask again later |
| `null` | | The operation is finished, whether it succeeded or not |

All of them are in `Payum\Core\Result\NextAction`. A minor release may add more, so code that acts on
`$result->next` needs a fallback for a type it does not know. See [Payment operations](../payment-operations.md#act-on-the-next-action).

`Payum::capture()`, which most applications use for their capture script, turns `Redirect`,
`PostRedirect` and `RenderTemplate` into a response, and sends the customer to the token's after URL
when `next` is null. It throws a `LogicException` for `Challenge` and `Poll`, because neither has one
obvious HTTP shape. If your gateway returns either, say so in its documentation: the application has
to dispatch the command itself and act on the result. See [Payment operations](../payment-operations.md).

## Status

`$result->status` is the payment's state after this operation. `Payum\Core\Result\PaymentStatus` has
these cases:

`New`, `Pending`, `Authorized`, `Captured`, `PartiallyCaptured`, `Refunded`, `PartiallyRefunded`,
`Disputed`, `ChargedBack`, `Canceled`, `Failed`, `Expired`, `Suspended`, `PaidOut`, `Unknown`.

Three of them usually arrive after the customer has left, through a webhook or a sync:

| Status | Means |
| :--- | :--- |
| `PartiallyCaptured` | Only part of the authorized amount was taken. `capturedAmount` says how much |
| `Disputed` | The cardholder opened a dispute. The merchant still has the money, but may lose it |
| `ChargedBack` | The dispute was lost and the money is gone. Not the same as `Refunded`, which the merchant chose |

`isSuccessful()` is true for `Authorized`, `Captured`, `PartiallyCaptured`, `Refunded`,
`PartiallyRefunded` and `PaidOut` when there is no failure. `Disputed` and `ChargedBack` are neither
successful nor failed, because the customer did pay.

The enum is string-backed. One value does not match its case name: `PaidOut` is stored as `'payedout'`,
so compare cases, not strings.

### Null means nothing changed

Leave the status null when the operation concluded nothing about the payment. That is what `failed()`
does by default: a declined refund leaves a captured payment captured, and a declined capture leaves the
payment where it was, so the customer can try another card. Pass a status only when the failure is
final:

```php
RefundResult::failed($failure);                          // the payment is unchanged
CaptureResult::failed($failure, PaymentStatus::Failed);  // this payment is finished
```

### Accepted means the outcome comes later

Many PSPs only accept a refund, a void or a capture of an authorization when you ask, and send the
outcome later by webhook. Return `accepted()` for that, with the PSP's id for the request and its
response:

```php
return RefundResult::accepted($response['pspReference'], $response);
```

The status is null, so the payment keeps the status it had: a captured payment stays `Captured` while
its refund is in progress, and stays `Captured` if the PSP refuses the refund. `isAccepted()` is true,
so the application can tell this apart from a result that concluded nothing. Your notify handler sets
the final status when the webhook arrives. See [Webhooks](webhooks.md).

Use `pending()` only when the payment itself is not finished: the customer is at a checkout page, or
the PSP is still processing a new payment such as a bank transfer. A `pending()` result moves the
payment to `Pending`. `RefundResult` and `CancelResult` have no `pending()`, because a refund or a void
in progress never makes the payment pending.

### Payum records the status

A handler never writes the status onto the payment. Payum does it after the handler returns, when the
payment implements `Payum\Core\Model\StatusAwareInterface`, and dispatches a `StatusChanged` event when
the status really moved. A webhook delivered twice therefore announces the change once. See
[Payment status](../payment-status.md) for the application side and [Events](../events.md) for the
events.

### The list is closed

Map what your PSP reports onto these cases. Never put a PSP's own status string in `$result->status`:
an application has to be able to act on a payment without knowing which gateway took it.

When the PSP reports something none of the cases describes, report the nearest case, or `Unknown` when
there is none, and keep the PSP's value in `raw`. If the gap is real,
[open an issue](https://github.com/Payum/Payum/issues) proposing a new case. Say what the payment's
money is doing in that state and why no existing case covers it, and link the documentation of the PSPs
that report it.

## Failures and exceptions

Payum separates the two kinds of things that go wrong:

* **A decline is a result.** A refused card or a customer who abandoned 3-D Secure is a normal business
  outcome. Return it with `failed()` and a `Failure`.
* **A fault is an exception.** An unreachable host, a rejected API key or a malformed response is not
  something the customer can fix. Let it throw.

A `Failure` says why, in terms every gateway shares, and keeps the PSP's own details:

```php
new Failure(FailureReason::InsufficientFunds, code: '51', message: 'Insufficient funds');

$result->failure->reason;        // FailureReason
$result->failure->code;          // the PSP's own code, as it sent it
$result->failure->message;       // the PSP's own message
$result->failure->isRetriable();
```

Map each of your PSP's decline codes onto a `FailureReason`: `Declined`, `InsufficientFunds`,
`ExpiredCard`, `Fraud`, `AuthenticationRequired`, `Configuration`, `Network`, `RateLimited` or `Unknown`.
Only `Network` and `RateLimited` are retriable. Retrying a declined card declines again, and some PSPs
treat repeated attempts as card testing.

The PSP's message is not written for customers, so an application should review it before showing it.

## Notify results

A notify handler returns a `NotifyResult`. It has no next action, because nobody is waiting on a page,
and it carries the HTTP answer the PSP gets:

```php
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NotifyResult;

NotifyResult::handled(PaymentStatus::Captured, transactionId: 'ch_1');
NotifyResult::handled(PaymentStatus::Captured, Acknowledgement::ok('[accepted]'));
NotifyResult::ignored();
```

See [Webhooks](webhooks.md) for what each one answers.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
