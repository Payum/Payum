# Paypal Pro Hosted. Get it started.

Introduction: https://developer.paypal.com/docs/classic/products/website-payments-pro-hosted-solution

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/paypal-pro-hosted-nvp php-http/guzzle6-adapter
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

    ->addGateway('aGateway', [
        'factory' => 'paypal_pro_hosted',
        'username'  => 'change it',
        'password'  => 'change it',
        'signature' => 'change it',
        'business'  => 'change it',
        'sandbox'   => true,
    ])

    ->getPayum()
;
```

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `paypal_pro_hosted`. The rest remain the same as described in basic [get it started](../../get-it-started.md) documentation.

Back to [index](../../index.md).
