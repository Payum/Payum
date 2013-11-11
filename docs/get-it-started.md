# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using [Paypal Pro Checkout](https://www.paypal.com/webapps/mpp/paypal-payments-pro).
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
Here we just extend it and describe [Paypal Pro Checkout](https://www.paypal.com/webapps/mpp/paypal-payments-pro) specific details.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/paypal-pro-checkout-nvp:*@stable"
```

Now you have all codes prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php
//config.php

use Buzz\Client\Curl;
use Payum\Paypal\ProCheckout\Nvp\PaymentFactory as PaypalProPaymentFactory;
use Payum\Paypal\ProCheckout\Nvp\Api as PaypalProApi;

// ...

$storages = array(
    'paypal-pro' => array(
        $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass)
    )
);

$payments = array(
    'paypal-pro' => PaypalProPaymentFactory::create(new PaypalProApi(
        new Curl,
        array(
            'username' => 'REPLACE IT',
            'password' => 'REPLACE IT',
            'partner' => 'REPLACE IT',
            'vendor' => 'REPLACE IT',
            'sandbox' => true
        )
    ))
);
```

## Prepare payment

```php
<?php
// prepare.php

include 'config.php';

$storage = $registry->getStorageForClass($detailsClass, 'paypal-pro');

$paymentDetails = $storage->createModel();
$paymentDetails['currency'] = 'USD';
$paymentDetails['amt'] = '1.00';
$paymentDetails['acct' = '5105105105105100';
$paymentDetails['expDate'] = '1214';
$paymentDetails['cvv2'] = '123';
$paymentDetails['billToFirstName'] = 'John';
$paymentDetails['billToLastName'] = 'Doe';
$paymentDetails['billToStreet'] = '123 Main St.';
$paymentDetails['billToCity] = 'San Jose';
$paymentDetails['billToState'] = 'CA';
$paymentDetails['billToZip'] = '95101';
$paymentDetails['billToCountry'] = 'US';
$storage->updateModel($paymentDetails);

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('paypal-pro');
$doneToken->setDetails($storage->getIdentificator($paymentDetails));
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('paypal-pro');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we configured Paypal Pro payment `config.php` and set details `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).