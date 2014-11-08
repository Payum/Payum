# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [redsys](http://www.redsts.es/).
We assume you already read [get it started](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.
Remember you need to talk with your bank to get your user data.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "crevillo/payum-redsys:*@dev"
```

## config.php

We have to only add a the payment factory. All the rest remain the same:

```php
<?php
//config.php

use Payum\Redsys\Api as RedsysApi;
use Payum\Redsys\PaymentFactory as RedsysPaymentFactory;

// ...

$payments['redsys'] = Be2BillPaymentFactory::create(new Redsys(array(
   'identifier' => 'REPLACE WITH YOURS',
   'password' => 'REPLACE WITH YOURS',
   'sandbox' => true
)));

## prepare.php

Here you have to modify a `paymentName` value. Set it to `redsys`.

## Next 

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).