# Get started

Some payments never go through an online payment provider: cash on delivery, a cheque, a bank transfer,
or store credit a customer already has with you. The Offline gateway handles these. It calls no external
API. It records each operation in the payment's details and reports the payment's status, so the rest of
your checkout works the same as with an online gateway.

This page assumes you have read the main [get started](../get-it-started.md) guide. It shows only what
changes for Offline.

## Install the package

Install the gateway with [Composer](https://getcomposer.org/):

```bash
composer require payum/offline
```

## Register the gateway

Register the gateway in `config.php`, with an `OfflineConfig` in place of the provider config:

```php
<?php
// config.php

use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Offline\OfflineConfig;

require __DIR__.'/vendor/autoload.php';

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addStorage(Payment::class, new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))
    ->registerGateway('cash', new OfflineConfig())
    ->getPayum();
```

In `prepare.php`, pass `'cash'` to `$payum->prepare()`. The other scripts stay as they are. When the
customer reaches `capture.php`, the gateway captures the payment (marks it as paid) at once and the
customer goes straight on to `done.php`.

## Offer several offline payment methods

Register the gateway once for each payment method, each under its own name and with its own config:

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addStorage(Payment::class, new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))
    ->registerGateway('cash', new OfflineConfig())
    ->registerGateway('bank_transfer', new OfflineConfig(paid: false))
    ->getPayum();
```

The customer picks a method, and you pass its name to `$payum->prepare()`. Each registration keeps its
own settings, so changing one does not affect the others.

`OfflineConfig` takes these options:

| Option | Default | What it changes |
| :--- | :--- | :--- |
| `paid` | `true` | Whether a payment counts as paid when it is captured or authorized. With `false`, it stays `Pending`. |
| `handlers` | `[]` | Handlers that replace the gateway's own for some operations. See [Run your own code for an operation](#run-your-own-code-for-an-operation). |

## Confirm a payment later

With `paid: false`, as for a bank transfer, a captured payment stays `Pending` until you see the money
arrive. Then set its `paid` detail and capture it again, for example from your back office:

```php
<?php

use Payum\Core\Command\CaptureCommand;

$payment->setDetails(['paid' => true] + (array) $payment->getDetails());

$result = $payum->getGateway('bank_transfer')->execute(CaptureCommand::forPayment($payment));

$payum->getStorage($payment::class)->update($payment);
```

`$result->status` is now `Captured`. A `paid` detail set on a payment always wins over the config, so
you can also set `'paid' => false` on one payment of a gateway registered with `paid: true`.

## Refund, cancel and pay out

The gateway answers every operation except notifications. You run them with the same commands as
for any other gateway, which [Payment operations](../payment-operations.md) describes:

| Command | What the Offline gateway does |
| :--- | :--- |
| `CaptureCommand` | `Captured` when paid, otherwise `Pending`. It always takes the full amount. |
| `AuthorizeCommand` | `Authorized` when paid, otherwise `Pending`. |
| `RefundCommand` | Refunds a captured payment, in full or in part. |
| `CancelCommand` | Cancels a new, pending or authorized payment. |
| `SyncCommand` | Reports the status stored in the payment's details. |
| `PayoutCommand` | `PaidOut`, unless the payout's `payout` detail is `false`, which leaves it `Pending`. |

A refund with an amount is a partial refund, and the payment becomes `PartiallyRefunded`. A refund with
no amount refunds what is left:

```php
<?php

use Payum\Core\Command\RefundCommand;

$gateway = $payum->getGateway('cash');

$gateway->execute(RefundCommand::forPayment($payment, 250)); // 2.50 of a 10.00 EUR payment
$gateway->execute(RefundCommand::forPayment($payment));      // the remaining 7.50 EUR

$payum->getStorage($payment::class)->update($payment);
```

The gateway tracks the refunded total in the payment's `refunded_amount` detail. A refund of a payment
that was never captured, or of more than is left, returns a failed result and leaves the payment as it
was. The same goes for cancelling a payment that was already captured. Check `$result->isFailed()`.

## Run your own code for an operation

Some offline methods need more than a status change. Paying with store credit, for example, has to take
the amount off the customer's balance. Write a handler for that operation and give it to the
registration. A handler is the class that runs one operation, such as capture, and returns its result:

```php
<?php

namespace App\Payment;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;

final class StoreCreditCaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly StoreCreditLedger $ledger,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $payment = $context->payment();

        if (! $this->ledger->withdraw($payment->getClientId(), $context->amount())) {
            return CaptureResult::failed(new Failure(FailureReason::InsufficientFunds));
        }

        return CaptureResult::captured();
    }
}
```

`StoreCreditLedger` stands for your own service. `$context->amount()` is the amount to capture, as a
`Money\Money`. Return a failed result when the customer cannot pay, rather than throwing, so the payment
stays where it was and the customer can choose another method.

Register the handler on the store credit gateway:

```php
<?php

use App\Payment\StoreCreditCaptureHandler;
use App\Payment\StoreCreditLedger;
use Payum\Offline\OfflineConfig;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(StoreCreditLedger::class, $ledger)
    ->registerGateway('cash', new OfflineConfig())
    ->registerGateway('store_credit', new OfflineConfig(handlers: [StoreCreditCaptureHandler::class]))
    ->getPayum();
```

The handler runs for captures on `store_credit` only, and `cash` keeps the gateway's own. Payum builds a
handler given by class name and passes it the services its constructor asks for, including the ones you
register with `addGlobalService()`. You can pass an instance instead:
`handlers: [new StoreCreditCaptureHandler($ledger)]`.

Give one handler per operation. Each one replaces the gateway's handler for the command it implements,
such as `RefundHandlerInterface` for a refund that puts the money back on the balance. The status your
handler returns is written to the payment's `status` detail, so refunds, cancels and syncs on that
payment see it. [Handlers](../gateways/handlers.md) covers what else the context gives a handler.

{% hint style="info" %}
`OfflineConfig` throws when two handlers implement the same command, or when a handler is for a command
the gateway does not take, such as `NotifyHandlerInterface`.
{% endhint %}

## Build your own offline payment method

When you reuse a payment method across applications, or want it to carry settings of its own, extend
`OfflineConfig`. Pass your handlers and defaults to the parent constructor:

```php
<?php

namespace App\Payment;

use Payum\Offline\OfflineConfig;

final class StoreCreditConfig extends OfflineConfig
{
    public function __construct(
        public readonly int $maximumPerOrder = 50000,
    ) {
        parent::__construct(handlers: [StoreCreditCaptureHandler::class]);
    }
}
```

Register it the same way: `->registerGateway('store_credit', new StoreCreditConfig())`. Your handlers
can take `StoreCreditConfig` in their constructor to read its settings, and the gateway's own handlers
still get it as an `OfflineConfig`.

To give the method its own name and logo on your payment method screen, or to add an operation the
Offline gateway does not have, also extend `OfflineGateway` and point the two classes at each other:

```php
<?php

namespace App\Payment;

use League\Uri\Uri;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Path;
use Payum\Offline\OfflineGateway;

final class StoreCreditGateway extends OfflineGateway
{
    public function name(): string
    {
        return 'Store credit';
    }

    public function logo(): Logo
    {
        return Path::create(__DIR__.'/store-credit.svg');
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://example.com/store-credit');
    }

    public function configClass(): string
    {
        return StoreCreditConfig::class;
    }

    public function handlers(): array
    {
        return [...parent::handlers(), StoreCreditNotifyHandler::class];
    }
}
```

Then add this method to `StoreCreditConfig`, so that registering the config builds your gateway:

```php
public function getGatewayClass(): string
{
    return StoreCreditGateway::class;
}
```

`handlers()` lists the operations the gateway takes, and Payum reads its capabilities from it, so
`StoreCreditNotifyHandler` here makes the gateway accept notifications. A handler you pass to the config
replaces one of the handlers listed there.

## The payment's details

The gateway keeps its state in the payment's details. You can read these keys, for example to show
them in your back office:

| Key | Holds |
| :--- | :--- |
| `paid` | Whether the payment counts as paid. |
| `status` | The status of the last operation, such as `captured` or `partially_refunded`. |
| `refunded_amount` | The total refunded so far, in the currency's smallest unit. |
| `amount`, `currency`, `number`, `description`, `client_email`, `client_id` | Copied from the payment when it is captured or authorized. |
| `payout`, `recipient_email`, `recipient_id` | The same for a payout. |

## Upgrading from 1.x

The 1.x registration, `addGateway('offline', ['factory' => 'offline'])`, still works in 2.x and goes
away in 3.0. Payments created with it keep their details, and the 2.x gateway reads them. A custom
`payum.action.*` entry, such as your own convert action, only reaches the 1.x gateway: move that code to
a handler as shown above. [Upgrade from 1.x](../upgrading.md) covers the rest of the upgrade.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
