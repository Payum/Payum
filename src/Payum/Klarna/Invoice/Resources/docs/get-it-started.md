# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product [Klarna Invoice](https://developers.klarna.com/en/invoice-and-part-payment/prepare-your-checkout-for-klarna).
Unfortunately, You cannot use Payum's order to purchase stuff. Only klarna specific format is supported.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/klarna-invoice:@stable"
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
use Payum\Klarn\Invoice\Config;
use Payum\Klarn\Invoice\PaymentFactory as KlarnaInvoicePaymentFactory;

$tokenStorage = new FilesystemStorage('/path/to/storage', 'App\Model\PaymentSecurityToken', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$detailsClass = 'App\Model\PaymentDetails';

$storages = array(
    $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass, 'id')
);

$payments = array();

$config = new Config;
$config->secret = 'EDIT IT';
$config->eid = 'EDIT IT';
$config->mode = \Klarna::BETA;

$payments['klarna_invoice'] => KlarnaInvoicePaymentFactory::create($config);

$registry = new SimpleRegistry($payments, $storages);

$tokenFactory = new GenericTokenFactory(
    $tokenStorage,
    $registry,
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

$payment = $payum->getPayment('klarna_invoice');
$payment->execute($getAddresses = new GetAddresses($pno));

$storage = $registry->getStorage($detailsClass);
$storage = $this->getPayum()->getStorage('Acme\PaymentBundle\Model\PaymentDetails');

$details = $storage->createModel();
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
$storage->updateModel($details);

$captureToken = $tokenFactory->createCaptureToken('klarna_invoice', $details, 'done.php');

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured Klarna Invoice `config.php` and set details `prepare.php`.
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
