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

$payments['offline'] = OfflinePaymentFactory::create();
```

## Prepare payment

```php
<?php
// prepare.php

use Payum\Offline\Constants;

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();

//if you don`t set this field payment is created with pending status
$paymentDetails[Constants::FIELD_PAID] = true;
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('offline', $paymentDetails, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we have to update `config.php` and create `prepare.php`.
Capture and Done scripts remain same for this and other payments.

Back to [index](index.md).