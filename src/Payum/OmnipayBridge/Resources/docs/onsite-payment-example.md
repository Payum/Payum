# Onsite payment example.

Onsite means a user is redirected to a payment site. 
There they enter credit card details, do purchase and come back to us.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Omnipay\Common\GatewayFactory;
use Payum\OmnipayBridge\OnsitePaymentFactory as OmnipayOnsitePaymentFactory;

//config.php

// ...

$gatewayFactory = new GatewayFactory;
$gatewayFactory->find();

$gateway = $gatewayFactory->create('PayPal_Express');
$gateway->setUsername('REPLACE IT');
$gateway->setPassword('REPLACE IT');
$gateway->setSignature('REPLACE IT');
$gateway->setTestMode(true);

$payments['paypal'] = OmnipayOnsitePaymentFactory::create($gateway);
```

## Prepare payment

```php
<?php

use Payum\Core\Security\SensitiveValue;

// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();
$paymentDetails['amount'] = 10.0;
$paymentDetails['currency'] = 'USD';
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('paypal', $paymentDetails, 'done.php');

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured be2bill payment in `config.php` and set details in `prepare.php`.
The `capture.php` and `done.php` scripts remain same.

Back to [index](index.md).