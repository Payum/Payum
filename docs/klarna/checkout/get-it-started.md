# Klarna Checkout. Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Klarna Checkout](https://developers.klarna.com/en/klarna-checkout).
Unfortunately, You cannot use Payum's order to purchase stuff. Only klarna specific format is supported.
We assume you already read basic [get it started](../../get-it-started.md).

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/klarna-checkout php-http/guzzle6-adapter
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
        'factory' => 'klarna_checkout',
        'merchant_id' => 'EDIT IT',
        'secret' => 'EDIT IT',
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

include __DIR__.'/config.php';

/** @var \Payum\Core\Storage\StorageInterface $storage */
$storage = $this->getPayum()->getStorage(ArrayObject::class);

$details = $storage->create();
$details['purchase_country'] = 'SE';
$details['purchase_currency'] = 'SEK';
$details['locale'] = 'sv-se';
$storage->update($details);

/** @var \Payum\Core\Security\TokenInterface $authorizeToken */
$authorizeToken = $payum->getTokenFactory()->createAuthorizeToken('klarna', $details, 'done.php');

/** @var \Payum\Core\Security\TokenInterface $notifyToken */
$notifyToken = $payum->tokenFactory()->createNotifyToken('klarna', $details);

$details['merchant'] = array(
    'terms_uri' => 'http://example.com/terms',
    'checkout_uri' => $authorizeToken->getTargetUrl(),
    'confirmation_uri' => $authorizeToken->getTargetUrl(),
    'push_uri' => $notifyToken->getTargetUrl()
);
$details['cart'] = array(
    'items' => array(
         array(
            'reference' => '123456789',
            'name' => 'Klarna t-shirt',
            'quantity' => 2,
            'unit_price' => 12300,
            'discount_rate' => 1000,
            'tax_rate' => 2500
         ),
         array(
            'type' => 'shipping_fee',
            'reference' => 'SHIPPING',
            'name' => 'Shipping Fee',
            'quantity' => 1,
            'unit_price' => 4900,
            'tax_rate' => 2500
         )
    )
);
$storage->update($details);

header("Location: ".$authorizeToken->getTargetUrl());
```

That's it. As you see we configured Klarna Checkout `config.php` and set details `prepare.php`.
[capture.php](../../examples/capture-script.md) and [done.php](../../examples/done-script.md) scripts remain same.

Back to [index](../../index.md).
