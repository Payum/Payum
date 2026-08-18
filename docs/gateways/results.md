# Results

Every result carries the same core, and each command adds what only it has.

```php
$result->status;         // PaymentStatus
$result->next;           // ?NextAction — what the customer must do, null when finished
$result->transactionId;  // ?string
$result->failure;        // ?Failure — set on a decline
$result->raw;            // array, the PSP's own payload

$result->isSuccessful();
$result->isFailed();
$result->requiresInteraction();  // $next !== null
```

| Result | Adds |
| :--- | :--- |
| `CaptureResult` | `capturedAmount` |
| `AuthorizeResult` | `authorizedAmount`, `expiresAt` |
| `RefundResult` | `refundedAmount` |
| `CancelResult` | — |
| `PayoutResult` | `paidOutAmount` |
| `SyncResult` | — |
| `NotifyResult` | `acknowledgement` |

### Building one

Use the named constructors; they set a coherent status for you.

```php
CaptureResult::captured($transactionId, $capturedAmount);
CaptureResult::pending(new Redirect($url));
CaptureResult::pending();                       // waiting on the PSP, nothing for the customer to do
CaptureResult::canceled();
CaptureResult::failed(new Failure(FailureReason::Declined, 'card_declined'));

AuthorizeResult::authorized($transactionId, $amount, $expiresAt);
RefundResult::refunded($transactionId, $amount);
RefundResult::partiallyRefunded($transactionId, $amount);
```

### `NotifyResult`

What a PSP's message amounted to.

```php
NotifyResult::handled(PaymentStatus::Captured, transactionId: 'txn_1');
NotifyResult::ignored();                                          // an event type you do not care about
NotifyResult::handled(PaymentStatus::Captured, Acknowledgement::ok('[accepted]'));
```

`ignored()` leaves the payment alone and still answers the PSP successfully. A null status means the
event concluded nothing about the payment.

It carries one thing no other result does: an `Acknowledgement`, which is the HTTP answer the PSP gets.
Leave it null and the answer is 204, which nearly every PSP accepts.

### Next actions

`next` says what the customer must do before the operation can finish. It describes intent, never an HTTP response, so a bridge turns it into one — and a JSON API can serialise it straight to a mobile client.

| Action | Carries | Means |
| :--- | :--- | :--- |
| `Redirect` | `url`, `statusCode`, `headers` | Send the customer to another URL |
| `PostRedirect` | `url`, `fields` | Same, by POST — normally a self-submitting form |
| `RenderTemplate` | `template` (Twig template name), `context` | Show a page the gateway owns: a card form, a wallet button |
| `Challenge` | `url`, `parameters`, `version` | A step-up, 3-D Secure being the usual one |
| `Poll` | `retryAfterSeconds` | Nothing to show; the PSP has not settled. Ask again later |
| `null` | | Finished, one way or another |

```php
<?php
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NextAction\RenderTemplate;

$result = $gateway->execute(CaptureCommand::forToken($token));

match (true) {
    $result->next instanceof Redirect       => header('Location: ' . $result->next->url),
    $result->next instanceof PostRedirect   => print $twig->render('post.html.twig', [
        'url' => $result->next->url,
        'fields' => $result->next->fields,
    ]),
    $result->next instanceof RenderTemplate => print $payum->renderer()->render(
        $result->next->template,
        $result->next->context,
    ),
    default                                 => header('Location: ' . $token->getAfterUrl()),
};
```

`Payum::capture()` already does this for `Redirect`, `PostRedirect` and `RenderTemplate` and returns a Symfony response, so most applications never write the `match` themselves. It redirects to the token's after URL when `next` is null, and throws for `Challenge` and `Poll` rather than reporting a payment as finished when it is not — dispatch the command yourself when you need to act on those. See [Templates](templates.md) for what a gateway has to declare before `RenderTemplate` will resolve.

### Status

`$result->status` is the payment's state after this operation:

`New`, `Pending`, `Authorized`, `Captured`, `Refunded`, `PartiallyRefunded`, `Canceled`, `Failed`, `Expired`, `Suspended`, `PaidOut`, `Unknown`.

It is string-backed, so it can be persisted or sent as JSON. One backing value does not match its case name — `PaidOut` is backed by `'payedout'` — so compare the case rather than the spelling.

**It is null when the operation concluded nothing about the payment.** A refund that was declined leaves a captured payment captured; a capture that was declined leaves the payment where it was, so the customer can try another card. Say otherwise only when the failure really is terminal:

```php
RefundResult::failed($failure);                          // the payment is unchanged
CaptureResult::failed($failure, PaymentStatus::Failed);  // this payment is finished
```

A handler never writes to the payment. It declares the payment's new state as part of its answer, the same way it declares what the customer must do next, and Payum commits it.

### Keeping the status on the payment

Implement `Payum\Core\Model\StatusAwareInterface` on your payment — or your payout — and Payum keeps it current after every command:

```php
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\PaymentStatus;

class Payment implements PaymentInterface, StatusAwareInterface
{
    #[ORM\Column(enumType: PaymentStatus::class)]
    private PaymentStatus $status = PaymentStatus::New;

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}
```

The status is then readable without going near a gateway, and queryable in whatever the payment is stored in — "every pending payment older than an hour" becomes an ordinary query.

Read it through `PaymentStatuses`, which returns null for a payment that does not track one — meaning nobody knows, which is not the same as `New`:

```php
use Payum\Core\Model\PaymentStatuses;

PaymentStatuses::of($payment);        // ?PaymentStatus
PaymentStatuses::isTracked($payment); // bool
```

A payment that does not implement the interface has no status stored, exactly as before.

### Failures and exceptions

The rule:

* **A decline is a result.** A refused card is an ordinary business outcome. It comes back as `$result->failure` with `PaymentStatus::Failed`.
* **An infrastructure fault is an exception.** An unreachable host, a rejected API key, a malformed config. Let it throw.

```php
$result->failure->reason;         // FailureReason
$result->failure->code;           // the PSP's own code, verbatim
$result->failure->message;        // the PSP's own message — review before showing a customer
$result->failure->isRetriable();
```

`FailureReason` is the portable taxonomy every gateway maps its codes onto: `Declined`, `InsufficientFunds`, `ExpiredCard`, `Fraud`, `AuthenticationRequired`, `Configuration`, `Network`, `RateLimited`, `Unknown`.

Only `Network` and `RateLimited` are retriable. Retrying a declined card just declines again, and some PSPs read it as card testing.

Next: [Templates](templates.md).
