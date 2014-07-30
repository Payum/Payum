# Get it started. Paypal ExpressCheckout.

In this chapter we are going to talk about [Paypal ExpressCheckout](https://www.paypal.com/webapps/mpp/express-checkout) integration.
We assume you already read [Payum's get it started documentation](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/get-it-started.md).
Here we just extend it and describe Paypal specific details.

_**Note**: If you are working with symfony2 framework look at the bundle [documentation instead](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md)._

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/paypal-express-checkout-nvp:*@stable"
```

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.


```php
<?php

use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory as PaypalExpressPaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

$payments['paypal_es'] = PaypalExpressPaymentFactory::create(new Api(array(
   'username'  => 'change it',
   'password'  => 'change it',
   'signature' => 'change it',
   'sandbox'   => true,
)));
```

## Prepare payment

```php
<?php
// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();
$paymentDetails['PAYMENTREQUEST_0_CURRENCYCODE'] = 'EUR';
$paymentDetails['PAYMENTREQUEST_0_AMT'] = 1.23;
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('paypal_es', $paymentDetails, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we configured Paypal ExpressCheckout `config.php` and set details `prepare.php`.
[`capture.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/capture-script.md) and [`done.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/done-script.md) scripts remain same.

Back to [index](index.md).
