# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using [Paypal Pro Checkout](https://www.paypal.com/webapps/mpp/paypal-payments-pro).
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
Here we just extend it and describe [Paypal Pro Checkout](https://www.paypal.com/webapps/mpp/paypal-payments-pro) specific details.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/paypal-pro-checkout-nvp:*@stable"
```

Now you have all codes prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php
//config.php

use Buzz\Client\Curl;
use Payum\Paypal\ProCheckout\Nvp\PaymentFactory as PaypalProPaymentFactory;
use Payum\Paypal\ProCheckout\Nvp\Api as PaypalProApi;

// ...

$payments['paypal-pro'] = PaypalProPaymentFactory::create(new PaypalProApi(
    new Curl,
    array(
        'username' => 'REPLACE IT',
        'password' => 'REPLACE IT',
        'partner' => 'REPLACE IT',
        'vendor' => 'REPLACE IT',
        'sandbox' => true
    )
));
```

## Prepare payment

```php
<?php

use Payum\Core\Security\SensitiveValue;

// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();
$paymentDetails['currency'] = 'USD';
$paymentDetails['amt'] = '1.00';
$paymentDetails['acct' = new SensitiveValue('5105105105105100');
$paymentDetails['exp_date'] = new SensitiveValue('1214');
$paymentDetails['cvv2'] = new SensitiveValue('123');
$paymentDetails['billtofirstname'] = 'John';
$paymentDetails['billtolastname'] = 'Doe';
$paymentDetails['billtostreet'] = '123 Main St.';
$paymentDetails['billtocity'] = 'San Jose';
$paymentDetails['billtostate'] = 'CA';
$paymentDetails['billtozip'] = '95101';
$paymentDetails['billtocountry'] = 'US';
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('paypal-pro', $paymentDetails, 'create_recurring_payment.php');

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured Paypal Pro payment `config.php` and set details `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).
