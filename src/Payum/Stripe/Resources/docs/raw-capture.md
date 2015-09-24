# Raw capture 

In the [get it started](https://github.com/Payum/Stripe/blob/master/Resources/docs/get-it-started.md) we showed how to use the library with unified interface or in other words Payment model.
Sometimes you need completely custom solution.

## prepare.php

Installation and configuration are same and we have to modify only a prepare part. 

Here you have to modify a `gatewayName` value. Set it to `stripe_js` or any other you want.
The rest remain the same as described basic [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) documentation.

```php
<?php
// prepare.php

use Payum\Core\Model\ArrayObject;

include 'config.php';

$gatewayName = 'stripe_js';

$storage = $payum->getStorage(ArrayObject::class);

$details = $storage->create();
$details['amount'] = '10.00'; 
$details['currency'] = 'USD';
$details['card'] = new SensitiveValue(
    'number' => '4242424242424242', 
    'expiryMonth' => '6', 
    'expiryYear' => '2016', 
    'cvv' => '123',
);

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

## Next

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported gateways](https://github.com/Payum/Core/blob/master/Resources/docs/supported-gateways.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).