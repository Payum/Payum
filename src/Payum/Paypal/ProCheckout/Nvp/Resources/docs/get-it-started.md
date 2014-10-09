# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Paypal ExpressCheckout](https://www.paypal.com/webapps/mpp/paypal-payments-pro).
We assume you already read [get it started](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/paypal-pro-checkout-nvp:*@stable"
```

## config.php

We have to only add a the payment factory. All the rest remain the same:


```php
<?php
//config.php

use Buzz\Client\Curl;
use Payum\Paypal\ProCheckout\Nvp\PaymentFactory as PaypalProPaymentFactory;
use Payum\Paypal\ProCheckout\Nvp\Api as PaypalProApi;

// ...

$payments['paypal_pro'] = PaypalProPaymentFactory::create(new PaypalProApi(
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

## prepare.php

Here you have to modify a `paymentName` value. Set it to `paypal_pro`.

## Next 

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).
