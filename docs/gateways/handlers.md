# Handlers

A handler answers exactly one command. There is one interface per command, so both sides of `handle()` are typed.

| Command | Handler interface |
| :--- | :--- |
| `CaptureCommand` | `Payum\Core\Handler\CaptureHandlerInterface` |
| `AuthorizeCommand` | `Payum\Core\Handler\AuthorizeHandlerInterface` |
| `RefundCommand` | `Payum\Core\Handler\RefundHandlerInterface` |
| `CancelCommand` | `Payum\Core\Handler\CancelHandlerInterface` |
| `PayoutCommand` | `Payum\Core\Handler\PayoutHandlerInterface` |
| `SyncCommand` | `Payum\Core\Handler\SyncHandlerInterface` |

```php
<?php
namespace Acme\Payum\Handler;

use Acme\Payum\Api\AcmeApi;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Result\RefundResult;

final class RefundHandler implements RefundHandlerInterface
{
    public function __construct(private readonly AcmeApi $api)
    {
    }

    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        $refund = $this->api->refund(
            $context->state()['charge_id'],
            $command->amount,
        );

        return null === $command->amount
            ? RefundResult::refunded($refund['id'], $refund['amount'])
            : RefundResult::partiallyRefunded($refund['id'], $refund['amount']);
    }
}
```

### Mapping a handler to its command

You do not. Listing the class in `handlers()` is the whole mapping:

```php
public function handlers(): array
{
    return [CaptureHandler::class, RefundHandler::class];
}
```

Payum reads the handler interface you implemented, and takes the command from its `handle()` signature. A handler cannot claim a command it does not implement, and there is no attribute or array to keep in sync.

PHP will not let one class declare `handle()` twice, so **a handler serves exactly one command**. Two handlers claiming the same command is an error at boot.

### The constructor

Take the api. Take anything else with the lifetime of the gateway — a storage, a renderer, a logger, a PSR-18 client. It is plain dependency injection, so the signature is yours.

Do not take the config. The api already holds it, and a handler reaching for a credential usually means the api is missing a method.

Do not take the payment, the token or the HTTP request. Those belong to a single execution and arrive on the context.

### The context

| Method | What it gives you |
| :--- | :--- |
| `state()` | The PSP state carried across requests, as an `ArrayObject` |
| `subject()` | What the command operates on, resolved from the command or loaded from its token |
| `payment()` | The subject when it is a payment, else null |
| `payout()` | The subject when it is a payout, else null |
| `token()` | The token this execution arrived on, if any |
| `httpRequest()` | The inbound request as PSR-7 |
| `tokens()` | The token factory, for minting a notify or second-hop URL |
| `gateway()` | The gateway currently executing — its name, logo, config class |
| `execute()` | Dispatch a sub-command |
| `previous()` | The enclosing executions, when a handler dispatched into another |
| `command()` | The command being handled |

### Capture runs more than once

This is the part to understand before writing a redirect gateway.

The PSP sends the customer back to the capture token's **own URL** — the same URL that started the capture. So Payum verifies the token again and dispatches the *identical* `CaptureCommand` a second time. The handler works out which pass it is on by reading the state it wrote during the first.

```php
<?php
final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(private readonly AcmeApi $api)
    {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        if (isset($context->httpRequest()->getQueryParams()['cancelled'])) {
            return CaptureResult::canceled();
        }

        // Second pass: a checkout exists, so the customer has been and come back.
        if ($state['checkout_id']) {
            $checkout = $this->api->retrieveCheckout($state['checkout_id']);

            return 'paid' === $checkout['status']
                ? CaptureResult::captured($checkout['charge_id'], $checkout['amount'])
                : CaptureResult::pending(raw: $checkout);
        }

        // First pass: open the checkout and send the customer away.
        $checkout = $this->api->createCheckout([
            'return_url' => $context->token()?->getTargetUrl(),
            'cancel_url' => $context->token()?->getTargetUrl() . '?cancelled=1',
            'amount' => $command->amount ?? $context->payment()?->getTotalAmount(),
            'currency' => $context->payment()?->getCurrencyCode(),
        ]);

        $state['checkout_id'] = $checkout['id'];

        return CaptureResult::pending(new Redirect($checkout['url']));
    }
}
```

Payum does not track which pass you are on, which is deliberate: some gateways need one pass, some need three, and a 3-D Secure step-up can add one at any point. The handler owns that decision.

### State

`$context->state()` is the PSP state that has to survive between HTTP requests. It is an `ArrayObject` over the payment's details.

```php
$state = $context->state();

$state['checkout_id'];             // null for a missing key, no isset needed
$state['checkout_id'] = 'chk_1';   // visible to Payum, no write-back call
$state->defaults(['currency' => 'EUR']);
$state->validateNotEmpty(['checkout_id']);
$state->toUnsafeArray();           // unwraps SensitiveValue — use when sending to the PSP
```

Payum writes it back onto the payment after the handler returns, and persists it when it was the one that loaded the payment. A payment you handed to the command yourself stays yours to persist.

Writing happens even when the handler throws. A checkout id written just before a failure has to survive, or the retry opens a second checkout and the customer can be charged twice.

Keep `SensitiveValue` wrapped in state. Only `toUnsafeArray()` unwraps it, and that is for the moment you send it to the PSP — not for storage or logs.

### Talking to the PSP

Everything goes through the api. That is what makes a handler testable without a network, and what keeps retry, logging and redaction in one place instead of scattered across handlers.

```php
<?php
namespace Acme\Payum\Api;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
    ) {
    }

    public function createCheckout(array $parameters): array { /* … */ }
    public function retrieveCheckout(string $id): array { /* … */ }
    public function refund(string $chargeId, ?int $amount = null): array { /* … */ }
}
```

Every argument is already a container entry, so this api needs no service definition at all.

Next: [Results](results.md).
