# Payment operations

Taking a payment is more than one call to the provider. You may reserve money first and take it later,
give some of it back, call off a payment the customer abandoned, or ask the provider what happened to a
payment. Payum runs each of these as a command that you execute on a gateway, and every command returns
a result that says what happened and what, if anything, the customer has to do next.

This page shows how to run each operation from your application. [Get started](get-it-started.md)
walks through a first payment; this page goes further.

## Set up

The samples use a made-up gateway registered as `acme`, Payum's own payment model, and a `config.php`
that every script includes:

```php
<?php
// config.php

use Acme\Payum\AcmeConfig;
use GuzzleHttp\Psr7\ServerRequest;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Storage\FilesystemStorage;
use Psr\Http\Message\ServerRequestInterface;

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/respond.php';

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addStorage(Payment::class, new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))
    ->addGlobalService(ServerRequestInterface::class, ServerRequest::fromGlobals())
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

`Payum\Core\Model\Payment` records the status of each operation on itself. To pay with your own order
or payment entity instead, see [Use your own payment model](your-order-integration.md). The file storage keeps this
sample short; use a database storage for real payments, as [Storages](storages.md) describes.

Register the current request as the `ServerRequestInterface` service. Gateways read the query string,
headers and body of the incoming request from it, and the request Payum builds by default carries no
headers. `ServerRequest` comes from `guzzlehttp/psr7`, and any PSR-7 implementation works.
`respond.php` is defined [further down](#act-on-the-next-action).

## Execute a command

Build a command for the operation and pass it to the gateway's `execute()` method:

```php
<?php

use Payum\Core\Command\RefundCommand;

$gateway = $payum->getGateway('acme');

$result = $gateway->execute(RefundCommand::forPayment($payment));

if ($result->isSuccessful()) {
    // the money is on its way back to the customer
}
```

`$payum->getGateway()` takes the name you registered the gateway under. `execute()` runs the gateway's
handler for that command and returns a result object. It does not throw for a declined card or a refused
refund, so always look at the result.

Every command lives in `Payum\Core\Command`:

| Command | Operation |
| :--- | :--- |
| `CaptureCommand` | Take the money. On a redirect gateway, this is the step the customer goes through. |
| `AuthorizeCommand` | Reserve the money on the customer's card without taking it. |
| `RefundCommand` | Give money back, in full or in part, after it was captured. |
| `CancelCommand` | Call off a payment before money moves, such as voiding an authorization. |
| `PayoutCommand` | Send money from you to a recipient. |
| `SyncCommand` | Ask the provider for the current state and record it. |
| `NotifyCommand` | Read a notification the provider sent. [Payment notifications](instant-payment-notification.md) covers it. |

### Build a command from a payment or from a token

Each command has two named constructors:

```php
<?php

use Payum\Core\Command\CaptureCommand;

CaptureCommand::forPayment($payment);   // you have the payment in hand
CaptureCommand::forToken($token);       // the customer arrived on a token URL
```

Use `forToken()` in a script the customer's browser reaches through a token URL, such as `capture.php`.
Payum loads the payment the token points at and saves it again when the command finishes. Use
`forPayment()` for work your application starts itself, such as a refund from an admin screen or a cron
job. Payum updates the payment object you pass, but it does not save it, so store it yourself afterwards:

```php
$result = $gateway->execute(RefundCommand::forPayment($payment));

$payum->getStorage($payment::class)->update($payment);
```

A token is a random hash in a URL that names a gateway and a payment.
[Tokens and payment URLs](tokens.md) explains how to create and verify one.

## Read the result

Every result extends `Payum\Core\Result\Result` and has these properties:

| Property | What it holds |
| :--- | :--- |
| `status` | The payment's `PaymentStatus` after the operation, or `null` when the operation says nothing new about it. |
| `next` | What the customer has to do before the operation can finish, or `null` when it is finished. |
| `transactionId` | The provider's reference for this operation, when it gave one. |
| `failure` | Why the operation did not succeed, or `null`. |
| `raw` | The provider's own response, as an array. |

Three methods answer the usual questions:

```php
$result->isSuccessful();          // finished and did not fail
$result->isFailed();              // declined, or the payment is now failed
$result->requiresInteraction();   // the customer has to do something first, see $result->next
```

`isSuccessful()` is true for `Authorized`, `Captured`, `PartiallyCaptured`, `Refunded`,
`PartiallyRefunded` and `PaidOut`. A `Pending` result is neither successful nor failed. A bank transfer
waiting to clear is not finished yet. [Payment status](payment-status.md) lists every status and what it
means.

Some results carry more. Read the amount that actually moved from the result, because a provider can
capture or pay out less than you asked for:

| Result | Extra properties |
| :--- | :--- |
| `CaptureResult` | `capturedAmount` (minor units), `capturedMoney` |
| `AuthorizeResult` | `authorizedAmount`, `authorizedMoney`, `expiresAt` (when the hold lapses, if the provider says) |
| `RefundResult` | `refundedAmount`, `refundedMoney` |
| `PayoutResult` | `paidOutAmount`, `paidOutMoney` |
| `NotifyResult` | `acknowledgement` (what Payum answers the provider with) |

The `*Money` properties are `Money\Money` objects and are set only when the gateway reported one. The
`*Amount` properties are integers in the currency's smallest unit. [Money and currencies](money.md)
explains the two forms.

## Declines and errors

Payum separates a payment that did not go through from code that could not run. A decline is a result. A
fault is an exception.

A declined card, a refused refund or a customer who gave up on 3-D Secure comes back as a result whose
`failure` is set:

```php
<?php

use Payum\Core\Result\FailureReason;

$result = $gateway->execute(CaptureCommand::forPayment($payment));

if ($result->failure) {
    $result->failure->reason;          // a FailureReason, such as FailureReason::InsufficientFunds
    $result->failure->code;            // the provider's own error code
    $result->failure->message;         // the provider's own message
    $result->failure->isRetriable();   // true only for Network and RateLimited
}
```

`FailureReason` is the same set for every gateway: `AuthenticationRequired`, `Configuration`, `Declined`,
`ExpiredCard`, `Fraud`, `InsufficientFunds`, `Network`, `RateLimited` and `Unknown`. Branch on it rather
than on the provider's code, and your retry logic works with any gateway.

{% hint style="warning" %}
Do not retry a failure that `isRetriable()` reports as not retriable. A declined card declines again, and
some providers treat repeated attempts as card testing. Do not show `failure->message` to a customer
without reviewing it, because it is whatever text the provider sent.
{% endhint %}

A failed operation usually leaves the payment's status where it was, so a customer whose card was
declined can try another card. The result's `status` is set only when the gateway knows the payment
itself has moved, for example to `Failed`.

An unreachable provider, a malformed configuration or a server error from the provider is thrown as
an exception. Executing a command the gateway has no handler for throws
`Payum\Core\Exception\CommandNotSupportedException`, whose message names the commands the gateway does
support. See [Check what a gateway supports](#check-what-a-gateway-supports) to avoid it.

## Act on the next action

When `$result->next` is set, the operation is not finished and the customer has something to do. Payum
describes it as data, and your application decides how to send it to the browser. There are five types,
all in `Payum\Core\Result\NextAction`:

| Next action | What to do |
| :--- | :--- |
| `Redirect` | Redirect the customer to `url`, with `statusCode` (302 by default) and `headers`. |
| `PostRedirect` | Send the customer to `url` by POST, with `fields` as the form fields. Usually rendered as a form that submits itself. |
| `RenderTemplate` | Render the Twig template `template` with `context` and show it. `$payum->renderer()` does this with your layout. |
| `Challenge` | The customer must pass a step-up check such as 3-D Secure at `url`, with `parameters` and the protocol `version`. The gateway's documentation says how to present it. |
| `Poll` | Nothing to show yet: the provider has not settled. Show a "we are checking" page or wait for a [notification](instant-payment-notification.md). `retryAfterSeconds` says when to look again, if the provider says. |

Gateways send the customer back to the URL that started the operation, and the same command runs
again. The gateway keeps its progress on the payment, so it carries on from where it stopped. That is
why a capture script runs more than once for one payment.

The scripts below share one function that turns a result into a response. Put it in a file of its own,
for example `respond.php`, and include it from `config.php`:

```php
<?php
// respond.php

use Payum\Core\Payum;
use Payum\Core\Result\NextAction\Poll;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;

function respond(Payum $payum, Result $result, TokenInterface $token): void
{
    $next = $result->next;

    if (null === $next) {
        $payum->getHttpRequestVerifier()->invalidate($token);

        header('Location: '.($token->getAfterUrl() ?: '/'));

        return;
    }

    if ($next instanceof Redirect) {
        foreach ($next->headers as $name => $value) {
            header($name.': '.$value);
        }

        header('Location: '.$next->url, true, $next->statusCode);

        return;
    }

    if ($next instanceof PostRedirect) {
        echo '<form method="post" action="'.htmlspecialchars($next->url).'">';
        foreach ($next->fields as $name => $value) {
            echo '<input type="hidden" name="'.htmlspecialchars((string) $name).'" value="'.htmlspecialchars((string) $value).'">';
        }
        echo '<noscript><button type="submit">Continue</button></noscript></form>';
        echo '<script>document.forms[0].submit();</script>';

        return;
    }

    if ($next instanceof RenderTemplate) {
        echo $payum->renderer()->render($next->template, $next->context);

        return;
    }

    if ($next instanceof Poll) {
        header('Refresh: '.($next->retryAfterSeconds ?? 5));
        echo 'We are checking your payment. This page refreshes by itself.';

        return;
    }

    throw new LogicException(sprintf('Add a case to respond() for %s.', $next::class));
}
```

When nothing is left to do, the function deletes the token, so the URL cannot run the operation again,
and redirects to the token's after URL. A `Poll` page refreshes the same URL, which runs the command
again until the provider has settled. `Challenge` falls through to the exception. Add the handling your
gateway's documentation describes.

## Capture a payment

Capture means taking the customer's money. A customer-facing capture starts with a capture token, which
`Payum::prepare()` creates after it saves the payment:

```php
<?php
// prepare.php

use Payum\Core\Model\Payment;

require __DIR__.'/config.php';

$payment = new Payment();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(1000);
$payment->setDescription('Order 1001');

$token = $payum->prepare('acme', $payment);

header('Location: '.$token->getTargetUrl());
```

The token's URL points at `capture.php`. Its after URL points at `done.php`, where the customer lands at
the end. Pass a path as the third argument to send this payment somewhere else instead of `done.php`.

### The capture script

The capture script verifies the token, runs the capture and sends the customer where the gateway asks:

```php
<?php
// capture.php

require __DIR__.'/config.php';

$payum->capture()->send();
```

`capture()` reads the token from the current request and checks that it was issued for this URL. It
throws `Payum\Core\Exception\InvalidArgumentException` when the token is missing, unknown or already
deleted, or was issued for another URL. It turns `Redirect`, `PostRedirect` and `RenderTemplate` into a
Symfony `Response`, and redirects to the after URL once the capture has nothing left to ask. The same
script works for every gateway, and it runs once per step the customer goes through.

In a framework controller, pass the Symfony HttpFoundation request you already have and return the
response: `return $payum->capture($request);`. `capture()` needs `symfony/http-foundation`, which Payum
does not install for you.

### Run the capture yourself

`capture()` throws a `LogicException` for a `Challenge` or a `Poll`, and it leaves the capture token in
storage. If your gateway uses either next action, or you want the token deleted, run the command
yourself and hand the result to `respond()`:

```php
<?php
// capture.php

use GuzzleHttp\Psr7\ServerRequest;
use Payum\Core\Command\CaptureCommand;

require __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify(ServerRequest::fromGlobals());

$result = $payum->getGateway($token->getGatewayName())->execute(CaptureCommand::forToken($token));

respond($payum, $result, $token);
```

`verify()` does the same checks as `capture()`, on a PSR-7 request.

### Capture part of the amount

Pass an amount to capture less than the payment's total, for example when only part of an order ships:

```php
<?php

use Money\Money;
use Payum\Core\Command\CaptureCommand;

$gateway->execute(CaptureCommand::forPayment($payment, Money::EUR(600)));
$gateway->execute(CaptureCommand::forPayment($payment, 600));   // the same, in minor units of the payment's currency
```

With no amount, the command captures the payment's full total. A partial capture needs a gateway that
declares `Capability::PartialCapture`. Payum does not check this for you, so
[check the gateway's capabilities](#check-what-a-gateway-supports) first. The gateway reports
`PartiallyCaptured` when the provider took less than the total.

## Show the outcome

The done script is where the customer lands at the end, whether the payment went through or not. It
reads the status Payum recorded on the payment and decides what to show:

```php
<?php
// done.php

use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Result\PaymentStatus;

require __DIR__.'/config.php';

$payment = $payum->done();

echo match (PaymentStatuses::of($payment)) {
    PaymentStatus::Captured, PaymentStatus::Authorized => 'Thank you, your payment went through.',
    PaymentStatus::Pending => 'Your payment is being processed. We will email you when it clears.',
    PaymentStatus::Canceled => 'You canceled the payment.',
    default => 'Your payment did not go through.',
};
```

`done()` verifies the token, deletes it so a reload cannot run your order logic twice, and returns the
payment. `PaymentStatuses::of()` returns `null` for a payment class of your own that does not implement
`StatusAwareInterface`, which the `default` arm covers.

Do not fulfil an order only from the done script. A customer can close the browser before reaching it,
and a `Pending` payment can clear hours later. Listen for the `StatusChanged` event to act on every
change, wherever it came from. See [Payment status](payment-status.md#react-when-the-status-changes).

## Authorize now, capture later

An authorization reserves the amount on the customer's card without taking it. You capture it later, for
example when the order ships, or cancel it if the order is canceled. Authorizing goes through the browser
the same way a capture does, through an authorize token:

```php
<?php
// prepare.php

use Payum\Core\Security\TokenPurpose;

$payum->getStorage($payment::class)->update($payment);

$token = $payum->getTokenIssuer()->create(TokenPurpose::Authorize, 'acme', $payment, 'done.php');

header('Location: '.$token->getTargetUrl());
```

Always pass the after path for an authorize token. Unlike `prepare()`, the issuer does not fall back to
`done.php` here, and creating the token without one fails.

The token points at `authorize.php`. Payum has no shortcut for this script, so it uses `respond()`:

```php
<?php
// authorize.php

use GuzzleHttp\Psr7\ServerRequest;
use Payum\Core\Command\AuthorizeCommand;

require __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify(ServerRequest::fromGlobals());

$result = $payum->getGateway($token->getGatewayName())->execute(AuthorizeCommand::forToken($token));

respond($payum, $result, $token);
```

When the order ships, capture the authorized amount from your own code. No customer is present, so build
the command from the payment and save the payment afterwards:

```php
<?php

use Payum\Core\Command\CaptureCommand;

$result = $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

$payum->getStorage($payment::class)->update($payment);
```

An authorization does not last forever. `AuthorizeResult::$expiresAt` says when the hold lapses, if the
provider reports it.

## Refund a payment

A refund gives captured money back. With no amount it refunds everything captured:

```php
<?php

use Money\Money;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Money\Amount;

$gateway = $payum->getGateway('acme');

$gateway->execute(RefundCommand::forPayment($payment));                                  // in full
$gateway->execute(RefundCommand::forPayment($payment, Money::EUR(250)));                 // 2.50 EUR
$gateway->execute(RefundCommand::forPayment($payment, 250, 'damaged_item'));             // with a reason
$gateway->execute(RefundCommand::forPayment($payment, Amount::of($payment)->divide(2))); // half

$payum->getStorage($payment::class)->update($payment);
```

The third argument is a reason, which some providers show in their dashboard. A partial refund needs a
gateway that declares `Capability::PartialRefund`, and the gateway reports the payment as
`PartiallyRefunded`. Some providers settle refunds later and confirm them with a notification, in which
case the result is `Pending`.

A refund normally runs from your back office, as above. If you want a URL that runs it, for example a
link in an admin email, create a refund token. Its after path is optional:

```php
$token = $payum->getTokenIssuer()->create(TokenPurpose::Refund, 'acme', $payment);
```

The token points at `refund.php`, which runs `RefundCommand::forToken($token)` and calls `respond()`, as
`authorize.php` does. Without an after path, `respond()` sends the browser to `/` once the refund is
done.

## Cancel a payment

Cancel calls a payment off before the money moves: it voids an authorization you will not capture, or
abandons a payment the customer never completed. Money already captured needs a refund instead.

```php
<?php

use Payum\Core\Command\CancelCommand;

$result = $payum->getGateway('acme')->execute(CancelCommand::forPayment($payment, 'out_of_stock'));
```

The reason is optional. A cancel the provider refuses leaves the payment as it was, which is usually
still `Authorized`. `CancelCommand::forPayout()` stops a payout that has not gone out yet.

## Pay out money

A payout sends money from you to a recipient, such as a seller on a marketplace. It works on a
`Payum\Core\Model\Payout` rather than a payment, which carries a recipient instead of a customer:

```php
<?php

use Payum\Core\Command\PayoutCommand;
use Payum\Core\Model\Payout;

$payout = new Payout();
$payout->setRecipientEmail('seller@example.com');
$payout->setCurrencyCode('EUR');
$payout->setTotalAmount(5000);
$payout->setDescription('Sales for October');

$result = $payum->getGateway('acme')->execute(PayoutCommand::forPayout($payout, 'payout-october-42'));

$payum->getStorage(Payout::class)->update($payout);
```

The second argument is an idempotency key (see below). Providers often accept a payout and send it later,
so expect `Pending` first and `PaidOut` once it has gone out. `paidOutAmount` can be lower than you
asked for when the provider pays a batch in part.

When the payout needs someone to approve it in the browser, create a payout token with
`TokenPurpose::Payout` and an after path, and run `PayoutCommand::forToken($token)` from `payout.php`
with `respond()`, as `authorize.php` does.

## Ask the provider for the current state

A sync asks the provider what it thinks the payment's state is, and records the answer:

```php
<?php

use Payum\Core\Command\SyncCommand;

$result = $payum->getGateway('acme')->execute(SyncCommand::forPayment($payment));

$result->status;   // whatever the provider reports

$payum->getStorage($payment::class)->update($payment);
```

Use it behind a refresh button in an admin screen, or in a job that walks the payments still `Pending`
after a notification failed to arrive. A sync that fails leaves the stored status alone.
`SyncCommand::forPayout()` does the same for a payout. Not every provider lets you read a payment back,
so not every gateway supports it.

## Idempotency keys

An idempotency key stops a retried request from charging or paying out twice. Send the same key on every
retry of the same operation, and a provider that supports keys runs it only once. Use a value tied to the
operation, such as your refund's id, not a random one per attempt.

`CaptureCommand::forPayment()`, `AuthorizeCommand::forPayment()` and `PayoutCommand::forPayout()` take the
key as their last argument. For a refund or a cancel, use the constructor with named arguments:

```php
<?php

use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;

CaptureCommand::forPayment($payment, idempotencyKey: 'capture-order-1001');
new RefundCommand(payment: $payment, amount: 250, reason: 'damaged_item', idempotencyKey: 'refund-77');
new CancelCommand(subject: $payment, reason: 'out_of_stock', idempotencyKey: 'cancel-order-1001');
```

Payum hands the key to the gateway, which passes it on to providers that accept one. Payum does not
remember keys itself. A sync and a notification carry no key, because neither changes anything at the
provider.

## Check what a gateway supports

Not every provider can do every operation. Ask the gateway whether it has a handler for a command before
you show, say, a refund button:

```php
<?php

use Payum\Core\Command\RefundCommand;

if ($payum->getGateway('acme')->supportsCommand(RefundCommand::class)) {
    // show the refund button
}
```

Finer points, such as partial refunds or 3-D Secure, are capabilities the gateway class declares. Payum
creates a gateway class without arguments, so you can read them before any credentials exist:

```php
<?php

use Acme\Payum\AcmeGateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;

$gateway = new AcmeGateway();

$partialRefunds = $gateway instanceof DeclaresCapabilities
    && in_array(Capability::PartialRefund, $gateway->capabilities(), true);
```

`Payum\Core\Gateway\Capability` has a case for each operation, which a gateway gets by having the handler,
and a case for each finer point, which it declares:

| Capability | Means |
| :--- | :--- |
| `Authorize`, `Capture`, `Refund`, `Cancel`, `Payout`, `Sync` | The gateway runs that command. Check with `supportsCommand()`. |
| `Webhooks` | The gateway reads the provider's notifications. Check with `supportsCommand(NotifyCommand::class)`. |
| `PartialCapture`, `PartialRefund` | An amount below the total works on capture or refund. |
| `MultiCurrency` | The gateway takes more than one currency. |
| `Recurring` | The gateway supports recurring payments. |
| `StoredPaymentMethods` | The gateway can charge a saved card or account again. |
| `ThreeDSecure` | The gateway supports 3-D Secure. |

The gateway's own documentation lists what it supports. A gateway still written for Payum 1.x answers no
commands at all. See [Upgrade from 1.x](upgrading.md) if you still use one.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
