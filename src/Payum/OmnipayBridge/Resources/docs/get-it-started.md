# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Omnipay](https://github.com/omnipay/omnipay).
We assume you already read [get it started](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/omnipay-bridge:*@stable"
```

## config.php

We have to only add a the payment factory. All the rest remain the same:

```php
<?php

use Omnipay\Common\GatewayFactory;
use Payum\OmnipayBridge\PaymentFactory as OmnipayPaymentFactory;
use Payum\OmnipayBridge\OnsitePaymentFactory as OmnipayOnsitePaymentFactory;

//config.php

// ...

$gatewayFactory = new GatewayFactory;
$gatewayFactory->find();

$stripeGateway = $gatewayFactory->create('Stripe');
$stripeGateway->setApiKey('REPLACE IT');
$stripeGateway->setTestMode(true);

$payments['stripe_omnipay'] = OmnipayPaymentFactory::create($stripeGateway);

// or onsite payment

$paypalGateway = $gatewayFactory->create('PayPal_Express');
$paypalGateway->setUsername('REPLACE IT');
$paypalGateway->setPassowrd('REPLACE IT');
$paypalGateway->setSignature('REPLACE IT');
$paypalGateway->setTestMode(true);

$payments['paypal_omnipay'] = OmnipayOnsitePaymentFactory::create($paypalGateway);

```

## prepare.php

Here you have to modify a `paymentName` value. Set it to `stripe_omnipay` or `paypal_omnipay` or any other you configure.

## Next

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).