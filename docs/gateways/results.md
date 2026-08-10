# Results

A handler returns a result rather than throwing. Every result carries the same core, and each command adds what only it has.

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

### Next actions

`next` says what the customer must do before the operation can finish. It describes intent, never an HTTP response, so a bridge turns it into one — and a JSON API can serialise it straight to a mobile client.

| Action | Carries | Means |
| :--- | :--- | :--- |
| `Redirect` | `url`, `statusCode`, `headers` | Send the customer to another URL |
| `PostRedirect` | `url`, `fields` | Same, by POST — normally a self-submitting form |
| `RenderTemplate` | `template`, `context` | Show a page the gateway owns: a card form, a wallet button |
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
    $result->next instanceof RenderTemplate => print $twig->render(
        $result->next->template,
        $result->next->context,
    ),
    default                                 => header('Location: ' . $token->getAfterUrl()),
};
```

`Payum::capture()` already does this for `Redirect` and `PostRedirect` and returns a Symfony response, so most applications never write the `match` themselves.

### Status

`PaymentStatus` replaces `GetHumanStatus` and `GetBinaryStatus`. The backing values are the 1.x strings, so anything already persisted stays valid.

`New`, `Pending`, `Authorized`, `Captured`, `Refunded`, `PartiallyRefunded`, `Canceled`, `Failed`, `Expired`, `Suspended`, `PaidOut`, `Unknown`.

`PartiallyRefunded` is the one genuinely new state — 1.x could not tell a half-refunded payment from a fully refunded one. `PaidOut` keeps the misspelled `'payedout'` backing value on purpose, so stored rows keep working.

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

Next: [Services](services.md).
