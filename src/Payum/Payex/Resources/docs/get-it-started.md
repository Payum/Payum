# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Payex](http://www.payexpim.com/).
We assume you already read [get it started](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/payex
```

## config.php

We have to only add the payment factory. All the rest remain the same:

```php
<?php
//config.php

// ...

$payexFactory = new \Payum\Payex\PaymentFactory();
$payments['payex'] = $payexFactory->create(array(
    'accountNumber' => 'REPLACE IT',
    'encryptionKey' => 'REPLACE IT',
    'sandbox' => true
));
```

## prepare.php

Here you have to modify a `paymentName` value. Set it to `payex`.

## Next 

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).