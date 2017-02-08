# Payex. Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Payex](http://www.payexpim.com/).
We assume you already read basic [get it started](../get-it-started.md).
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/payex php-http/guzzle6-adapter
```

## config.php

We have to only add the gateway factory. All the rest remain the same:

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('gatewayName', [
        'factory' => 'payex',
        'account_number' => 'REPLACE IT',
        'encryption_key' => 'REPLACE IT',
        'sandbox' => true
    ])

    ->getPayum()
;
```

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `payex`. The rest remain the same as described in basic [get it started](../get-it-started.md) documentation.

Back to [index](../index.md).