<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Stripe Direct.

Use this method if you already get a credit card details somehow, and you just have to send them to Stripe.

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
    ->addGateway('gatewayName', [
        'factory' => 'stripe_checkout',
        'publishable_key' => 'EDIT IT',
        'secret_key' => 'EDIT IT'
    ])

    ->getPayum()
;
```

## prepare.php

Do next:

```php
<?php
// prepare.php

use Payum\Core\Model\CreditCard;

// ...

/** @var \Payum\Core\Model\Payment $payment */

$card = new CreditCard();
$card->setNumber('4111111111111111');
$card->setExpireAt(new \DateTime('2018-10-10'));
$card->setSecurityCode(123);

$payment->setCreditCard($card);
```

Here you have to modify a `gatewayName` value. Set it to `stripe`. And create and populate a credit card object. 
The rest remain the same as described in basic [get it started](../get-it-started.md) documentation.
 
Back to [index](../index.md).
