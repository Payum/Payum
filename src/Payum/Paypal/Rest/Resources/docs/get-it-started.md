# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product.
We assume you already read [payum's get it started documentation](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/paypal-rest"
```

## Configuration

```php
<?php
//config.php

// ...

$paypalRestFactory = new \Payum\Paypal\Rest\PaymentFactory();

$payments['paypal_rest'] = $paypalRestFactory->create(array(
    'client_id' => 'REPLACE IT',
    'client_secret' => 'REPLACE IT',
    'config_path' => 'REPLACE IT',
));
```

## Prepare payment

```php
<?php
// prepare.php

include 'config.php';

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

$storage = $payum->getStorage($paypalRestPaymentDetailsClass);

$paymentDetails = $storage->create();
$storage->update($paymentDetails);

$payer = new Payer();
$payer->payment_method = "paypal";

$amount = new Amount();
$amount->currency = "USD";
$amount->total = "1.00";

$transaction = new Transaction();
$transaction->amount = $amount;
$transaction->description = "This is the payment description.";

$captureToken = $tokenFactory->createCaptureToken('paypalRest', $paymentDetails, 'create_recurring_payment.php');

$redirectUrls = new RedirectUrls();
$redirectUrls->return_url = $captureToken->getTargetUrl();
$redirectUrls->cancel_url = $captureToken->getTargetUrl();

$paymentDetails->intent = "sale";
$paymentDetails->payer = $payer;
$paymentDetails->redirect_urls = $redirectUrls;
$paymentDetails->transactions = array($transaction);

$storage->update($paymentDetails);

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we configured Paypal Rest `config.php` and set details `prepare.php`.
[capture.php](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/capture-script.md) and [done.php](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/done-script.md) scripts remain same.

Back to [index](index.md).
