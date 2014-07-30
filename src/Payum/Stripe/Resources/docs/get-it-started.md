# Get it started. Stripe.js.

In this chapter we are going to talk about [Stripe.js](https://stripe.com/docs/stripe.js) integration.
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).
Here we just extend it and describe [Stripe](https://stripe.com/) specific details.

_**Note**: If you are working with symfony2 framework look at the bundle [documentation instead](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md)._

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

$payments['stripe_js'] = StripePaymentFactory::createJs(
    new Keys('publishable_key', 'secret_key')
);
```

## Prepare payment

```php
<?php
// prepare.php

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

That's it. As you see we configured Stripe.Js `config.php` and set details `prepare.php`.
[`capture.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/capture-script.md) and [`done.php`](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/done-script.md) scripts remain same.

Back to [index](index.md).
