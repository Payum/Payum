# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using klarna checkout.
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
Here we just extend what was said there to match klarna's specific details.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/klarna-checkout:@stable"
```

Now you have all the code prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Payum\Klarn\Checkout\GlobalStateSafeConnector;
use Payum\Klarn\Checkout\PaymentFactory as KlarnaPaymentFactory;

//config.php

// ...

$payments['klarna_checkout'] => KlarnaPaymentFactory::create(new GlobalStateSafeConnector(
    new Klarna_Checkout_Connector('REPLACE_WITH_YOUR_SECRET'),
    'REPLACE_WITH_YOUR_MERCHANT_ID',
    Payum\Klarna\Checkout::Constants::BASE_URI_SANDBOX
));
```

## Prepare payment

```php
<?php

// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);
$storage = $this->getPayum()->getStorage('Acme\PaymentBundle\Model\PaymentDetails');

$details = $storage->createModel();
$details['purchase_country'] = 'SE';
$details['purchase_currency'] = 'SEK';
$details['locale'] = 'sv-se';
$storage->updateModel($details);

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('klarna_checkout');
$doneToken->setDetails($storage->getIdentificator($paymentDetails));
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('klarna_checkout');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

$notifyToken = $tokenStorage->createModel();
$notifyToken->setPaymentName('klarna_checkout');
$notifyToken->setDetails($storage->getIdentificator($paymentDetails));
$notifyToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/notify.php?payum_token='.$notifyToken->getHash());
$notifyStorage->updateModel($notifyToken);

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
$storage->updateModel($details);

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured be2bill payment `config.php` and set details `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).