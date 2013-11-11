# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using [be2bill](http://www.be2bill.com/).
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
Here we just extend it and describe [be2bill](http://www.be2bill.com/) specific details.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/be2bill:*@stable"
```

Now you have all codes prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Payum\Be2Bill\Api as Be2BillApi;
use Payum\Be2Bill\PaymentFactory as Be2BillPaymentFactory;

//config.php

// ...

$storages = array(
    'be2bill' => array(
        $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass)
    )
);

$payments = array(
    'be2bill' => Be2BillPaymentFactory::create(new Be2BillApi(new Curl, array(
       'identifier' => 'REPLACE WITH YOURS',
       'password' => 'REPLACE WITH YOURS',
       'sandbox' => true
    )
);
```

## Prepare payment

```php
<?php
// prepare.php

include 'config.php';

$storage = $registry->getStorageForClass($detailsClass, 'be2bill');

$paymentDetails = $storage->createModel();
$paymentDetails['amount'] = 100; //1$
$paymentDetails['clientemail'] = 'buyer@example.com';
$paymentDetails['clientuseragent'] = 'Firefox';
$paymentDetails['clientip'] = 192.168.0.1;
$paymentDetails['clientident'] = 'payerId';
$paymentDetails['description'] = 'Payment for digital stuff';
$paymentDetails['orderid'] = 'orderId';
$paymentDetails['setCardcode'] = '4111111111111111';
$paymentDetails['cardcvv'] = 123;
$paymentDetails['cardfullname'] = 'John Doe';
$paymentDetails['cardvaliditydate'] = $data['card_expiration_date']);
$storage->updateModel($paymentDetails);

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('be2bill');
$doneToken->setDetails($storage->getIdentificator($paymentDetails));
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('be2bill');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we configured be2bill payment `config.php` and set details `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).