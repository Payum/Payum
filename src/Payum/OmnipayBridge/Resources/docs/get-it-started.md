# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using [Omnipay library](https://github.com/omnipay/omnipay).
We assume you already read [payum's get it started documentation](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
Here we just extend it and describe [Omnipay](https://github.com/omnipay/omnipay) specific details.

_**Note**: If you are working with symfony2 framework look at the bundle [documentation instead](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md)._

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/omnipay-bridge:*@stable" "payum/xxx:*@stable"
```

_**Note**: Where payum/xxx is a payum package, for example it could be omnipay/stripe. Look at [supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md) to find out what you can use._

_**Note**: Use payum/payum if you want to install all payments at once._

Now you have all codes prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Omnipay\Common\GatewayFactory;
use Payum\OmnipayBridge\PaymentFactory as OmnipayPaymentFactory;

//config.php

// ...

$gatewayFactory = new GatewayFactory;
$gatewayFactory->find();

$stripeGateway = $gatewayFactory->create('Stripe');
$stripeGateway->setApiKey('REPLACE IT');
$stripeGateway->setTestMode(true);

$payments['stripe_omnipay'] = OmnipayPaymentFactory::create($stripeGateway);
```

## Prepare payment

```php
<?php

use Payum\Core\Security\SensitiveValue;

// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();
$paymentDetails['amount'] = '10.00';
$paymentDetails['currency'] = 'USD';
$paymentDetails['card'] = new SensitiveValue(array(
    'number' => '5555556778250000', //end zero so will be accepted
    'cvv' => 123,
    'expiryMonth' => 6,
    'expiryYear' => 16,
    'firstName' => 'foo',
    'lastName' => 'bar',
));
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('stripe_omnipay', $paymentDetails, 'done.php');

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured Omnipay Bridge `config.php` and set details `prepare.php`.
[`capture.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/capture-script.md) and [`done.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/done-script.md) scripts remain same.

Back to [index](index.md).
