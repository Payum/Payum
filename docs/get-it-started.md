# Get started

This page takes you through your first payment with Payum in plain PHP. You configure Payum, create
a payment, send the customer to the gateway (Payum's adapter for one payment provider) and read the
result when they come back.

## Install Payum

Install Payum, a gateway package, Symfony's HTTP foundation component and an HTTP client with
[Composer](https://getcomposer.org/):

```bash
composer require payum/core acme/payum-acme symfony/http-foundation php-http/guzzle7-adapter
```

`acme/payum-acme` stands for the package of your payment provider. It is the example gateway that
[Build a gateway](develop-gateway-with-payum.md) creates, so you can build it and follow this page
without an account at any provider. [Supported gateways](supported-gateways.md) lists the packages
that exist.

The other two packages are needed by the scripts on this page. `symfony/http-foundation` provides
the request and response objects that `$payum->capture()` and `$payum->done()` work with. Gateways
talk to their provider through a PSR-18 HTTP client, and `php-http/guzzle7-adapter` is one. Any
[client implementation](https://packagist.org/providers/php-http/client-implementation) works.

## How a payment flows

A payment takes four PHP scripts:

1. `config.php` builds the `Payum` object. Every other script includes it.
2. `prepare.php` creates the payment and redirects the customer to the capture URL.
3. `capture.php` runs the gateway. Capture means taking the customer's money. The gateway may send
   the customer to the provider's site and back before it finishes.
4. `done.php` is where the customer lands at the end, paid or not. It reads the payment status.

Each redirect carries a token, a random single-use value that tells the next script which payment
and gateway to work with. Put the four scripts in the same directory. Payum builds the capture and
done URLs from the URL of the script that creates the token, so it expects `capture.php` and
`done.php` next to `prepare.php`.

## Create the payment model

Payum records the status of a payment on the payment itself when its class implements
`StatusAwareInterface`. Create `Payment.php` with a payment class that does:

```php
<?php
// Payment.php

namespace App;

use Payum\Core\Model\Payment as BasePayment;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Result\PaymentStatus;

class Payment extends BasePayment implements StatusAwareInterface
{
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

`Payum\Core\Model\Payment` holds the amount, currency, customer and the provider's data. It does not
track a status, which is why you extend it. In your application this is usually your own order or
payment entity. [Use your own payment model](your-order-integration.md) shows how, and
[Payment status](payment-status.md) lists the statuses.

## Configure Payum

Create `config.php`. It registers a storage for your payment class and the gateway, under a name of
your choice:

```php
<?php
// config.php

use Acme\Payum\AcmeConfig;
use App\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Storage\FilesystemStorage;

require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/Payment.php';

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addStorage(Payment::class, new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

`registerGateway()` takes the gateway's config object, which holds its credentials. `'acme'` is the
name you use for this gateway in the other scripts. You can register several gateways, each under
its own name.

`addDefaultStorages()` stores tokens as files in the system temporary directory. `addStorage()` does
the same for your payment class, using its number as the file name. Payum needs a storage for every
model class you pay with, and looks it up by the exact class name.

{% hint style="warning" %}
`FilesystemStorage` is for trying Payum out. The system can clear its temporary directory at any
time, and your payments and tokens go with it. Register database storages before you take real
payments.
{% endhint %}

Payum ships storages for Doctrine ORM, Doctrine MongoDB ODM and Laminas DB. See
[Storages](storages.md) for the setup. `PayumBuilder` has more options, such as sharing a logger with
every gateway or replacing one of Payum's services. [Configure Payum](di/getting-started.md) covers
them.

## Create the payment

Create `prepare.php`. It fills in a payment, stores it and redirects the customer to the capture
URL:

```php
<?php
// prepare.php

use App\Payment;

require __DIR__.'/config.php';

$payment = new Payment();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(1000);
$payment->setDescription('Order 1001');
$payment->setClientId('customer-42');
$payment->setClientEmail('customer@example.com');

$captureToken = $payum->prepare('acme', $payment);

header('Location: '.$captureToken->getTargetUrl());
```

Amounts are in the currency's smallest unit, so `1000` with `'EUR'` is 10.00 EUR. `prepare()` saves
the payment in the storage registered for its class and returns a capture token. The token's target
URL points at `capture.php`, and it carries a second token for `done.php`.

### Send the customer somewhere other than done.php

Pass a third argument to send the customer to another script at the end of this payment:

```php
$captureToken = $payum->prepare('acme', $payment, 'thanks.php');
```

To change it for every payment, set the `done` path on the builder in `config.php`:

```php
$payum = (new PayumBuilder())
    ->setGenericTokenFactoryPaths(['done' => 'thanks.php'])
    // ...
    ->getPayum();
```

[Tokens and payment URLs](tokens.md) lists the other paths and how Payum builds the URLs.

## Capture the payment

Create `capture.php`. It works for every gateway, so you can copy it as it is:

```php
<?php
// capture.php

require __DIR__.'/config.php';

$payum->capture()->send();
```

`capture()` reads the token from the current request, checks that it was issued for this URL, and
asks the gateway to capture the payment. It returns a Symfony `Response`, and `send()` sends it to the
browser.

When the gateway needs the customer to do something, such as log in at the provider, the response
redirects them there or shows the page the gateway asks for. The provider sends the customer back to
the same URL, and the script runs again. When the gateway has nothing left to ask, the response
redirects to `done.php`.

If you already have a Symfony `Request` object, for example in a framework controller, pass it:
`$payum->capture($request)`. Without one, `capture()` builds the request from PHP's globals.

{% hint style="info" %}
Some gateways ask the application to poll the provider or to run a 3-D Secure challenge.
`capture()` cannot turn those into a response and throws a `LogicException`. Run the capture command
yourself for those gateways, as [Payment operations](payment-operations.md) shows.
{% endhint %}

## Check the result

Create `done.php`. It reads the payment status and shows it to the customer:

```php
<?php
// done.php

use Payum\Core\Result\PaymentStatus;

require __DIR__.'/config.php';

$payment = $payum->done();

if (PaymentStatus::Captured === $payment->getStatus()) {
    echo 'Thank you, your payment of '.$payment->getTotalAmount().' '.$payment->getCurrencyCode().' went through.';
} else {
    echo 'Your payment did not go through: '.$payment->getStatus()->value;
}
```

`done()` checks the token, deletes it and returns the payment. The customer reaches `done.php` whether
the payment succeeded or failed, so this is where you decide what happens next: mark the order paid,
send a receipt or show an error. Because the token is gone, reloading the page throws an exception
instead of running that logic twice.

`PaymentStatus::Pending` means the provider has not confirmed the payment yet. Some providers confirm
it later, by calling your server. [Payment status](payment-status.md) explains each status.

## Next steps

- Providers that confirm payments from their own servers need a notify script. See
  [Payment notifications](instant-payment-notification.md).
- To refund, cancel or authorize a payment, or to act on the gateway's answer yourself, see
  [Payment operations](payment-operations.md).
- Before you go live, move payments and tokens to a database with [Storages](storages.md).
- If you use a framework, [Integrate with a framework](di/framework-integration.md) shows how to share
  your application's services with Payum.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
