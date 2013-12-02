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

use Payum\Core\Security\SensitiveValue;

// prepare.php

include 'config.php';

$storage = $registry->getStorageForClass($detailsClass, 'be2bill');

$paymentDetails = $storage->createModel();
$paymentDetails['AMOUNT'] = 100; //1$
$paymentDetails['CLIENTEMAIL'] = 'buyer@example.com';
$paymentDetails['CLIENTUSERAGENT'] = 'Firefox';
$paymentDetails['CLIENTIP'] = 192.168.0.1;
$paymentDetails['CLIENTIDENT'] = 'payerId';
$paymentDetails['DESCRIPTION'] = 'Payment for digital stuff';
$paymentDetails['ORDERID'] = 'orderId';
$paymentDetails['CARDCODE'] = new SensitiveValue('4111111111111111');
$paymentDetails['CARDCVV'] = new SensitiveValue(123);
$paymentDetails['CARDFULLNAME'] = new SensitiveValue('John Doe');
$paymentDetails['CARDVALIDITYDATE'] = new SensitiveValue('10-16');
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

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured be2bill payment `config.php` and set details `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).