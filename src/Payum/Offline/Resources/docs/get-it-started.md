# Get it started.

In this chapter we are going to talk about offline payments. The offline payments is all about cash or cheque.
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
This chapter is based on that one.

_**Note**: If you are working with symfony2 framework look at the bundle [documentation instead](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md)._

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

That's it. As you see we configured Offline `config.php` and set details `prepare.php`.
[`capture.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/capture-script.md) and [`done.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/done-script.md) scripts remain same.

Back to [index](index.md).