# Get it started.

In this chapter we are going to talk about offline payments. The offline payments is all about cash or cheque.
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
This chapter is based on that one.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/offline:*@stable"
```

Now you have all required code downloaded, autoload configured.

## Configuration

Let's modify `config.php` a bit.

```php
<?php
//config.php

use Payum\Offline\PaymentFactory as OfflinePaymentFactory;

// ...

$storages = array(

    // other storages here

    'offline' => array(
        $detailsClass => new FilesystemStorage(__DIR__.'/storage', $detailsClass)
    )
);

$payments = array(

    // other storages here

    'offline' => OfflinePaymentFactory::create()
);
```

## Prepare payment

```php
<?php
// prepare.php

use Payum\Offline\Constants;

include 'config.php';

$storage = $registry->getStorageForClass($detailsClass, 'offline');

$paymentDetails = $storage->createModel();

//if you don`t set this field payment is created with pending status
$paymentDetails[Constants::FIELD_PAID] = true;
$storage->updateModel($paymentDetails);

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('offline');
$doneToken->setDetails($storage->getIdentificator($paymentDetails));
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('offline');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we have to update `config.php` and create `prepare.php`.
Capture and Done scripts remain same for this and other payments.

Back to [index](index.md).