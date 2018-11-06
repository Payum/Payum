<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Paypal Express Checkout. Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Paypal ExpressCheckout](https://www.paypal.com/webapps/mpp/express-checkout).
We assume you already read basic [get it started](../../get-it-started.md).
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/paypal-express-checkout-nvp php-http/guzzle6-adapter
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

    ->addGateway('gatewayName', [
        'factory' => 'paypal_express_checkout',
        'username'  => 'change it',
        'password'  => 'change it',
        'signature' => 'change it',
        'sandbox'   => true,
    ])

    ->getPayum()
;
```

## prepare.php

Here you have to modify the `gatewayName` value. Set it to `paypal_express_checkout`. The rest remain the same as described in basic [get it started](../../get-it-started.md) documentation.

Back to [index](../../index.md).
