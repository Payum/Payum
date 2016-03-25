# Get it started. Paypal Masspay.

Mass Payments lets you send multiple payments in one batch.
It's a fast and convenient way to send commissions, rebates, rewards, and general payments.
You must have explicit permisson from PayPal to use Mass Payments.
You submit the payment information to PayPal in the form of a payment file.
PayPal processes each payment and notifies you when it is complete.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/paypal-masspay-nvp php-http/guzzle6-adapter
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
        'factory' => 'paypal_masspay'
        'username'  => 'change it',
        'password'  => 'change it',
        'signature' => 'change it',
        'sandbox'   => true,
    ])

    ->getPayum()
;
```

## prepare.php

Here we sends two users some money:

```php
<?php
//prepare.php

use Payum\Core\Model\ArrayObject;

include 'config.php';

$gatewayName = 'aGateway';

$storage = $payum->getStorage(ArrayObject::class);

$details = $storage->create();
$details['CURRENCYCODE'] = 'USD';
$details['RECEIVERTYPE'] = 'EmailAddress';
$details['L_EMAIL0'] = 'fooReceiver@example.com';
$details['L_AMT0'] = 100;
$details['L_EMAIL1'] = 'fooReceiver@example.com';
$details['L_AMT1'] = 200;
$storage->update($details);

$payoutToken = $payum->getTokenFactory()->createPayoutToken($gatewayName, $details, 'done.php');

header("Location: ".$payoutToken->getTargetUrl());
```

## Links

* https://developer.paypal.com/docs/classic/mass-pay/integration-guide/MassPayOverview/
* https://developer.paypal.com/docs/classic/mass-pay/integration-guide/MassPayUsingAPI/
* https://developer.paypal.com/docs/classic/mass-pay/integration-guide/MassPayUsingAPI/

Back to [index](index.md).
