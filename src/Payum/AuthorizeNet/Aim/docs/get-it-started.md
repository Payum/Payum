# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using [Authorize.Net AIM](http://www.authorize.net/).
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
Here we just extend it and describe [Authorize.Net](http://www.authorize.net/) specific details.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/authorize-net-aim:*@stable"
```

Now you have all codes prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\PaymentFactory as AuthorizeNetPaymentFactory;

//config.php

// ...

$authorizeNetAim = new AuthorizeNetAIM($loginId = 'REPLACE IT', $transactionKey = 'REPLACE IT');
$authorizeNetAim->setSandbox(true);

$storages = array(
    'authorize-net-aim' => array(
        $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass)
    )
);

$payments = array(
    'authorize-net-aim' => AuthorizeNetPaymentFactory::create($authorizeNetAim)
);
```

## Prepare payment

```php
<?php

use Payum\Security\SensitiveValue;

// prepare.php

include 'config.php';

$storage = $registry->getStorageForClass($detailsClass, 'authorize-net-aim');

$paymentDetails = $storage->createModel();
$paymentDetails['amount'] = 2; // 2$
$paymentDetails['cardNum'] = new SensitiveValue('4111111111111111');
$paymentDetails['expDate'] = new SensitiveValue('10/16');
$storage->updateModel($paymentDetails);

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('authorize-net-aim');
$doneToken->setDetails($storage->getIdentificator($paymentDetails));
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('authorize-net-aim');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured Authorize.Net payment `config.php` and set details `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).