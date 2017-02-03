# Klarna Invoice. Get it started.

In this chapter we are going to talk about the most common task: purchase of a product [Klarna Invoice](https://developers.klarna.com/en/invoice-and-part-payment/prepare-your-checkout-for-klarna).
Unfortunately, You cannot use Payum's order to purchase stuff. Only klarna specific format is supported.
We assume you already read basic [get it started](../../get-it-started.md).

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/klarna-invoice php-http/guzzle6-adapter
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
    ->addGateway('klarna', [
        'factory' => 'klarna_invoice',
        'eid' => 'EDIT IT',
        'secret' => 'EDIT IT',
        'country' => 'SE',
        'language' => 'SV',
        'currency' => 'SEK',
        'sandbox' => true,
    ])

    ->getPayum()
;
```

An initial configuration for Payum basically wants to ensure we have things ready to be stored such as
a token, or a payment details. We also would like to have a registry of various gateways supported and the place where they can store their information (e.g. payment details).

_**Note**: Consider using something other than `FilesystemStorage` in production. `DoctrineStorage` may be a good alternative._

First we have modify `config.php` a bit.
We need to add gateway factory and payment details storage.

## prepare.php

```php
<?php
// prepare.php

use Payum\Core\Model\ArrayObject;
use Payum\Klarna\Invoice\Request\Api\GetAddresses;

include __DIR__.'/config.php';

/** @var \Payum\Core\Payum $payum */

$gateway = $payum->getGateway('klarna_invoice');
$gateway->execute($getAddresses = new GetAddresses($pno));

$storage = $payum->getStorage(ArrayObject::class);

$details = $storage->create();
$details = array(
    /** @link http://developers.klarna.com/en/testing/invoice-and-account */
    'pno' => '410321-9202',
    'amount' => -1,
    'gender' => \KlarnaFlags::MALE,
    'articles' => array(
        array(
            'qty' => 4,
            'artNo' => 'HANDLING',
            'title' => 'Handling fee',
            'price' => '50.99',
            'vat' => '25',
            'discount' => '0',
            'flags' => \KlarnaFlags::INC_VAT | \KlarnaFlags::IS_HANDLING
        ),
    ),
    'billing_address' => $getAddresses->getFirstAddress()->toArray(),
    'shipping_address' => $getAddresses->getFirstAddress()->toArray(),
);
$storage->update($details);

$captureToken = $payum->getTokenFactory()->createCaptureToken('klarna_invoice', $details, 'done.php');

$_REQUEST['payum_token'] = $captureToken;

include __DIR__.'/capture.php';
```

That's it. As you see we configured Klarna Invoice `config.php` and set details `prepare.php`.
[capture.php](../../examples/capture-script.md) and [done.php](../../examples/done-script.md) scripts remain same.

Back to [index](../../index.md).
