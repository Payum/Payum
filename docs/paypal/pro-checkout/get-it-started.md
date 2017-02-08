# Paypal Pro Checkout. Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Paypal Pro Checkout](https://www.paypal.com/webapps/mpp/paypal-payments-pro).
We assume you already read basic [get it started](../../get-it-started.md).
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/paypal-pro-checkout-nvp"
```

## config.php

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('gatewayName', [
        'factory' => 'paypal_pro_checkout',
        'username' => 'REPLACE IT',
        'password' => 'REPLACE IT',
        'partner' => 'REPLACE IT',
        'vendor' => 'REPLACE IT',
        'sandbox' => true
    ])

    ->getPayum()
;
```

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `paypal_pro_checkout`. The rest remain the same as described in basic [get it started](../../get-it-started.md) documentation.

Back to [index](../../index.md).
