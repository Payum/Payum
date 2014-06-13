# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using [Omnipay library](https://github.com/adrianmacneil/omnipay).
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
Here we just extend it and describe [Omnipay](https://github.com/adrianmacneil/omnipay) specific details.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/omnipay-bridge:*@stable" "omnipay/stripe:~2.0"
```

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

$payments['stripe'] = OmnipayPaymentFactory::create($stripeGateway);
```

## Prepare payment

```php
<?php

use Payum\Core\Security\SensitiveValue;

// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();
$paymentDetails['amount'] = 10;
$paymentDetails['card'] = new SensitiveValue(array(
    'number' => '5555556778250000', //end zero so will be accepted
    'cvv' => 123,
    'expiryMonth' => 6,
    'expiryYear' => 16,
    'firstName' => 'foo',
    'lastName' => 'bar',
));
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('stripe', $paymentDetails, 'done.php');

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured Stripe via Omnipay payment `config.php` and set details `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).