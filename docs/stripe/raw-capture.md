# Stripe. Raw capture 

In the basic [get it started](../get-it-started.md) we showed how to use the library with unified interface or in other words Payment model.
Sometimes you need completely custom solution.

## prepare.php

Installation and configuration are same and we have to modify only a prepare part. 

Here you have to modify a `gatewayName` value. Set it to `stripe_js` or any other you want.
The rest remain the same as described in basic [get it started](../get-it-started.md) documentation.

```php
<?php
// prepare.php

use Payum\Core\Model\ArrayObject;
use Payum\Core\Security\SensitiveValue;

include __DIR__.'/config.php';

$gatewayName = 'stripe_js';

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage(ArrayObject::class);

$details = $storage->create();
$details['amount'] = '10.00'; 
$details['currency'] = 'USD';
$details['card'] = new SensitiveValue([
    'number' => '4242424242424242', 
    'expiryMonth' => '6', 
    'expiryYear' => '2016', 
    'cvv' => '123',
]);

$details["amount"] = '10.00';
$details["currency"] = 'USD';
$details["description"] = 'A description';
$details["card"] = 'aStripeToken';

// or
//
//$details["card"] = new SensitiveValue(array(
//   'number' => '4111111111111111',
//   'exp_month' => '10',
//   'exp_year' => '2018',
//   'cvc' => '123',
//));

$storage->update($details);

$captureToken = $payum->getTokenFactory()->createCaptureToken($gatewayName, $details, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

Back to [index](../index.md).