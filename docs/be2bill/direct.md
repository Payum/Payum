# Be2Bill Direct.

In this chapter we are going to talk about the most common task: purchase of a product using [be2bill](http://www.be2bill.com/).
We assume you already read basic [get it started](../get-it-started.md).
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/be2bill php-http/guzzle6-adapter
```

## config.php

Use this when you are going to ask for a credit card details at your side.
Use [offsite](offsite.md) chapter if you want to redirect user to be2bill side.

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()

    ->addGateway('be2bill_direct', [
        'factory' => 'be2bill_direct',
        'identifier' => 'REPLACE WITH YOURS',
        'password' => 'REPLACE WITH YOURS',
        'sandbox' => true,
    ])

    ->getPayum()
;
```

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `be2bill` or `be2bill_offsite`. The rest remain the same as described in basic [get it started](../get-it-started.md) documentation.

Back to [index](../index.md).