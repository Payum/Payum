# How Payum works

Payum gives your application one way to take payments, whichever provider handles them. You tell a
gateway what you want done by executing a command, and you get back a result that says what
happened and whether the customer still has something to do. This page explains those pieces and
how they fit together. Read [Get started](get-it-started.md) first if you have not run a payment yet.

## Gateways

A gateway is Payum's adapter for one payment provider (PSP, payment service provider). It comes in
its own Composer package and has a config class for its credentials. You register it on the builder
under a name:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

require __DIR__.'/vendor/autoload.php';

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();

$gateway = $payum->getGateway('acme');
```

The name is yours to choose. Register the same gateway twice under two names to use two accounts at
one provider. To keep gateway configs in a database instead of in code, see
[Store gateway configs in a database](configure-gateway-in-backend.md).

## Commands and results

Every payment operation is a command. You create the command for the payment and execute it on the
gateway:

```php
<?php

use Payum\Core\Command\RefundCommand;

$result = $gateway->execute(RefundCommand::forPayment($payment, 500));

if ($result->isSuccessful()) {
    // $result->status is PaymentStatus::PartiallyRefunded or PaymentStatus::Refunded
}
```

Payum has a command for each operation: `AuthorizeCommand` (reserve money on the customer's card),
`CaptureCommand` (take the money), `RefundCommand`, `CancelCommand`, `PayoutCommand` (send money to
someone), `SyncCommand` (ask the provider for the payment's current state) and `NotifyCommand` (handle
a message the provider sends to your server). A gateway supports the commands its provider offers.
Executing one it does not support throws a `CommandNotSupportedException`.

Each command returns a result with the same fields:

| Field | What it holds |
| :--- | :--- |
| `status` | The payment's status after the operation, a `PaymentStatus`, or null when the operation changed nothing |
| `next` | What the customer has to do next, or null when the operation is finished |
| `transactionId` | The provider's id for the transaction |
| `failure` | Why the operation failed, when it did |
| `raw` | The provider's own response data |

A declined card does not throw. `$result->failure` holds a reason such as
`FailureReason::InsufficientFunds` and the provider's own code. Problems that are not about the
payment, such as an unreachable provider or a wrong API key, throw an exception.

[Payment operations](payment-operations.md) shows each command from the application's side.

## Next actions

When the customer has to do something before the operation can finish, the result's `next` says
what. It describes the step. It is not an HTTP response, so you can turn it into a redirect, a page
or a JSON answer for a mobile app:

| Next action | What it asks for |
| :--- | :--- |
| `Redirect` | Send the customer to a URL |
| `PostRedirect` | Send the customer to a URL with a form post |
| `RenderTemplate` | Show a page from the gateway, such as a card form |
| `Challenge` | Run a 3-D Secure challenge, a check where the card's bank asks the customer to confirm |
| `Poll` | The provider has not decided yet. Ask again later |

`$payum->capture()` turns `Redirect`, `PostRedirect` and `RenderTemplate` into a Symfony response for
you. To change how gateway pages look, see [Customize payment pages](payment-pages.md).

## Payment status

The status says where a payment stands: `PaymentStatus::Captured`, `Pending`, `Failed`, `Refunded`
and so on. Payum writes it onto the payment after every command that changes it, if the payment
class implements `StatusAwareInterface`. Your code then reads the status from the payment, without
asking the provider. See [Payment status](payment-status.md).

## Tokens

The customer moves between your site and the provider's, so each step needs a URL that tells Payum
which payment and gateway to work with. Payum puts a token in that URL, a random value it keeps in
the token storage. `$payum->prepare()` creates the capture token for a payment, and
`$payum->capture()` and `$payum->done()` verify the token in the request before they do anything.

A token works only on the URL it was issued for, so a token for `done.php` cannot be used to run a
capture. `$payum->done()` deletes its token, so the done page runs once per payment. See
[Tokens and payment URLs](tokens.md).

## Storage

Payum stores two kinds of data: your payments and its tokens. Each goes in a storage, which is
Payum's interface to a database table or collection. You register one storage per model class.

A gateway keeps the provider's data, such as a checkout id, in the payment's details. After each
command Payum writes that data back onto the payment. When the command came from a token, Payum
loaded the payment and also saves it. When you passed the payment to the command yourself, as in
`RefundCommand::forPayment($payment)`, saving it is up to you.

See [Storages](storages.md) for Doctrine ORM, Doctrine MongoDB ODM and Laminas DB.

## Capture can run more than once

A capture often sends the customer away to the provider. When they come back, they land on the same
capture URL, and Payum executes the same capture command again. The gateway reads the data it saved
on the first run and carries on from there. Some providers need one pass, some need three, and a
3-D Secure check can add one.

Your capture script does not need to know about this. It runs the command and acts on the result,
every time.

## The container

`PayumBuilder` builds a [PHP-DI](https://php-di.org/) container for each gateway, on top of one
global container with the services every gateway shares: the token storage, the token issuer, the
request verifier, the PSR-18 HTTP client and the PSR-17 factories. Each gateway gets its own API
client with its own credentials, and all of them share the same HTTP client.

You replace a shared service once, on the builder, and every gateway uses it. In a framework, you
can hand Payum your application's container. See [Configure Payum](di/getting-started.md) and
[Integrate with a framework](di/framework-integration.md).

## Middleware and events

Every command passes through middleware on its way to the gateway and back. Payum's own middleware
saves the provider's data, records the status, fills in the variables of gateway templates and stops
a gateway that executes commands in an endless loop. You can add your own, for logging or locking,
and it runs for every gateway. See [Middleware](middleware.md).

Payum also dispatches PSR-14 events, such as `StatusChanged` when a payment's status moves. Listen
to them to fulfil an order or send a receipt. See [Events](events.md).

## Advanced: inside a gateway

This section is for gateway authors. You can skip it if you only use gateways.

A gateway class describes the gateway: its name, logo, config class and handlers. A handler answers
one command. There is one handler interface per command, and the gateway lists its handlers:

```php
<?php

namespace Acme\Payum;

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
        $refund = $this->api->refund($context->state()['charge_id'], $command->amount);

        return RefundResult::refunded($refund['id'], $refund['amount']);
    }
}
```

The handler gets its services through the constructor, from the gateway's container. The context
holds what belongs to this one execution: the payment, the token, the HTTP request and
`state()`, the provider's data that has to survive between requests.

The [Gateways](gateways/README.md) chapter covers each part. Start with
[Build a gateway](develop-gateway-with-payum.md), then read [Handlers](gateways/handlers.md) and
[Results](gateways/results.md).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
