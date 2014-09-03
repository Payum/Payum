# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using Klarna Invoice.
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/get-it-started.md).
Here we just extend what was said there to match klarna's specific details.

_**Note**: If you are working with symfony2 framework look at the bundle [documentation instead](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md)._

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/klarna-invoice:@stable"
```

Now you have all the code prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Payum\Klarn\Invoice\Config;
use Payum\Klarn\Invoice\PaymentFactory as KlarnaInvoicePaymentFactory;

//config.php

// ...

$config = new Config;
$config->secret = 'EDIT IT';
$config->eid = 'EDIT IT';
$config->mode = \Klarna::BETA;

$payments['klarna_invoice'] => KlarnaInvoicePaymentFactory::create($config);
```

## Prepare payment

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
[`capture.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/capture-script.md) and [`done.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/done-script.md) scripts remain same.

Back to [index](index.md).
