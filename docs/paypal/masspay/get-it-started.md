# Paypal Masspay. Get it started.

Mass Payments lets you send multiple payments in one batch.
It's a fast and convenient way to send commissions, rebates, rewards, and general payments.
You must have explicit permission from PayPal to use Mass Payments.
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
        'factory' => 'paypal_masspay',
        'username'  => 'change it',
        'password'  => 'change it',
        'signature' => 'change it',
        'sandbox'   => true,
    ])

    ->getPayum()
;
```

## prepare.php

Here we send 1$ to recipient@example.com user:

```php
<?php
//prepare.php

use Payum\Core\Model\Payout;

include __DIR__.'/config.php';

$gatewayName = 'aGateway';

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage(Payout::class);

$payout = $storage->create();
$payout->setCurrencyCode('USD');
$payout->setRecipientEmail('recipient@example.com');
$payout->setTotalAmount(100); // 1$
$storage->update($payout);

$payoutToken = $payum->getTokenFactory()->createPayoutToken($gatewayName, $payout, 'done.php');

header("Location: ".$payoutToken->getTargetUrl());
```

## Links

* https://developer.paypal.com/docs/classic/mass-pay/integration-guide/MassPayOverview/
* https://developer.paypal.com/docs/classic/mass-pay/integration-guide/MassPayUsingAPI/

Back to [index](../../index.md).
