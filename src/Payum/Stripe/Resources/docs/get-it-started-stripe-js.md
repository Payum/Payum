# Get it started (Stripe.js).

In this chapter we are going to talk about [Stripe.js](https://stripe.com/docs/stripe.js) integration.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/stripe:*@stable"
```

Now you have all codes prepared and ready to be used.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Payum\Stripe\PaymentFactory as StripePaymentFactory;
use Payum\Stripe\Keys;

//config.php

$twigLoader->addPath(__DIR__.'/vendor/payum/stripe/Resources/views', 'PayumStripe');

$payments['stripe_js'] = StripePaymentFactory::createJs(
    new Keys('publishable_key', 'secret_key'),
    $twig
);
```

## Prepare payment

```php
<?php
// prepare.php

use Payum\Payex\Api\OrderApi;

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$details = $storage->createModel();
$details["amount"] = 100;
$details["currency"] = 'USD';
$details["description"] = 'a description';
$storage->updateModel($details);

$captureToken = $tokenFactory->createCaptureToken('stripe_js', $details, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we configured Stripe.js payment in `config.php` and set details in `prepare.php`.
`capture.php` and `done.php` scripts remain same.

Back to [index](index.md).