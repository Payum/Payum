# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Klarna Checkout](https://developers.klarna.com/en/klarna-checkout).
Unfortunately, You cannot use Payum's order to purchase stuff. Only klarna specific format is supported.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/klarna-checkout:@stable"
```

## config.php

Now configuration. Let's start from defining some models.
First one is a `PaymentDetails`.
It will storage all the information related to the payment:

```php
<?php
namespace App\Model;

use Payum\Core\Model\ArrayObject;

class PaymentDetails extends ArrayObject
{
    protected $id;
}
```

The other one is `PaymentSecurityToken`.
We will use it to secure our payment operations:

```php
<?php
namespace App\Model;

use Payum\Core\Model\Token;

class PaymentSecurityToken extends Token
{
}
```

_**Note**: We provide Doctrine ORM\MongoODM mapping for these models to ease usage with doctrine storage._

Now we are ready to configure all the stuff:

```php
<?php
//config.php

use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Security\PlainHttpRequestVerifier;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Klarn\Checkout\Config;
use Payum\Klarn\Checkout\Constants;
use Payum\Klarn\Checkout\PaymentFactory as KlarnaPaymentFactory;

$tokenStorage = new FilesystemStorage('/path/to/storage', 'App\Model\PaymentSecurityToken', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$detailsClass = 'App\Model\PaymentDetails';

$storages = array(
    $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass, 'id')
);

$payments = array();

$config = new Config;
$config->merchantId = 'EDIT IT';
$config->secret = 'EDIT IT';

$payments['klarna_checkout'] => KlarnaPaymentFactory::create($config);

$payum = new SimpleRegistry($payments, $storages);

$tokenFactory = new GenericTokenFactory(
    $tokenStorage,
    $payum,
    'http://'.$_SERVER['HTTP_HOST'],
    'capture.php',
    'notify.php',
    'authorize.php'
);
```

An initial configuration for Payum basically wants to ensure we have things ready to be stored such as
a token, or a payment details. We also would like to have a registry of various payments supported and the place where they can store their information (e.g. payment details).

_**Note**: Consider using something other than `FilesystemStorage` in production. `DoctrineStorage` may be a good alternative._

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

## prepare.php

```php
<?php

// prepare.php

include 'config.php';

$storage = $payum->getStorage($detailsClass);
$storage = $this->getPayum()->getStorage('Acme\PaymentBundle\Model\PaymentDetails');

$details = $storage->create();
$details['purchase_country'] = 'SE';
$details['purchase_currency'] = 'SEK';
$details['locale'] = 'sv-se';
$storage->update($details);

$captureToken = $tokenFactory->createCaptureToken('klarna_checkout', $details, 'done.php');
$notifyToken = $tokenFactory->createNotifyToken('klarna_checkout', $details);

$details['merchant'] = array(
    'terms_uri' => 'http://example.com/terms',
    'checkout_uri' => $captureToken->getTargetUrl(),
    'confirmation_uri' => $captureToken->getTargetUrl(),
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

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we configured Klarna Checkout `config.php` and set details `prepare.php`.
[capture.php](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/capture-script.md) and [done.php](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/done-script.md) scripts remain same.

## Next 

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).