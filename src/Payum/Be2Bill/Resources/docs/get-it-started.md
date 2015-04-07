# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [be2bill](http://www.be2bill.com/).
We assume you already read [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/be2bill
```

## config.php

We have to only add the gateway factory. All the rest remain the same:

```php
<?php
//config.php

// ...

// direct payment, credit card form at your app side
$directBe2billFactory = new \Payum\Be2Bill\DirectGatewayFactory();
$gateways['be2bill'] = $directBe2billFactory->create(array(
   'identifier' => 'REPLACE WITH YOURS',
   'password' => 'REPLACE WITH YOURS',
   'sandbox' => true
));

// or offsite 

$offsiteBe2billFactory = new \Payum\Be2Bill\OffsiteGatewayFactory();
$gateways['be2bill_offsite'] = $offsiteBe2billFactory->create(array(
   'identifier' => 'REPLACE WITH YOURS',
   'password' => 'REPLACE WITH YOURS',
   'sandbox' => true
));
```

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `be2bill` or `be2bill_offsite`. The rest remain the same as described basic [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) documentation.


## Next 

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported gateways](https://github.com/Payum/Core/blob/master/Resources/docs/supported-gateways.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).