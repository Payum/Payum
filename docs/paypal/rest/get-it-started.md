<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Paypal Rest. Get it started.

In this chapter we are going to talk about the most common task: purchasing a product.
We assume you already read basic [get it started](../../get-it-started.md).


## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/paypal-rest php-http/guzzle6-adapter
```

## Configuration

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('gatewayName', [
        'factory' => 'paypal_rest',
        'client_id' => 'REPLACE IT', // Your PayPal REST API cliend ID.
        'client_secret' => 'REPLACE IT', // Your PayPal REST API client secret.
        'config_path' => 'REPLACE IT', // Point to the directory where your skd_config.ini is located.
    ])

    ->getPayum()
;
```
alternatively, set configuration via the `config` option. See PayPal's `sdk_config.ini` for available options.
```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('gatewayName', [
        'factory' => 'paypal_rest',
        'client_id' => 'REPLACE IT',
        'client_secret' => 'REPLACE IT',
        'config' => [
            'option1' => 'value1',
            'option2' => 'value2',
        ],
    ])

    ->getPayum()
;
```

## Prepare payment

```php
<?php
// prepare.php

include __DIR__.'/config.php';

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

$paypalRestPaymentDetailsClass = 'Payum\Paypal\Rest\Model\PaymentDetails';

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage($paypalRestPaymentDetailsClass);

$payment = $storage->create();
$storage->update($payment);

$payer = new Payer();
$payer->payment_method = "paypal";

$amount = new Amount();
$amount->currency = "USD";
$amount->total = "1.00";

$transaction = new Transaction();
$transaction->amount = $amount;
$transaction->description = "This is the payment description.";

$captureToken = $payum->getTokenFactory()->createCaptureToken('paypalRest', $payment, 'create_recurring_payment.php');

$redirectUrls = new RedirectUrls();
$redirectUrls->return_url = $captureToken->getTargetUrl();
$redirectUrls->cancel_url = $captureToken->getTargetUrl();

$payment->intent = "sale";
$payment->payer = $payer;
$payment->redirect_urls = $redirectUrls;
$payment->transactions = array($transaction);

$storage->update($payment);

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we configured Paypal Rest `config.php` and set details `prepare.php`.
[capture.php](../../examples/capture-script.md) and [done.php](../../examples/done-script.md) scripts remain same.
Here you have to modify a `gatewayName` value. Set it to `paypal_pro_checkout`. The rest remain the same as described in basic [get it started](../../get-it-started.md) documentation.

Back to [index](../../index.md).
