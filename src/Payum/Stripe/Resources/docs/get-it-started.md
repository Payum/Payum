# Get it started. Stripe.js.

In this chapter we are going to talk about the most common task: purchase of a product using [Stripe.js](https://stripe.com/docs/stripe.js).
We assume you already read [get it started](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/stripe
```

## config.php

We have to only add the payment factory. All the rest remain the same:

```php
<?php
//config.php

// ..

$stripeJsFactory = new \Payum\Stripe\JsPaymentFactory();
$payments['stripe_js'] = $stripeJsFactory->create(array(
    'publishable_key' => 'EDIT IT', 'secret_key' => 'EDIT IT'
));
```

## prepare.php

Here you have to modify a `paymentName` value. Set it to `stripe_js`.

## Next 

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).