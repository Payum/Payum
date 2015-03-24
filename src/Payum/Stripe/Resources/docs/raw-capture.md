# Raw capture 

In the [get it started](https://github.com/Payum/Stripe/blob/master/Resources/docs/get-it-started.md) we showed how to use the library with unified interface or in other words Order model. 
Sometimes you need completely custom solution.  

## config.php

Add a storage for the payment model:

```php
<?php
// config.php

// ...

$detailsClass = 'Payum\Core\Model\ArrayObject';

$storages = array(
    $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass),
    
    //put other storages
);
```

## prepare.php

Installation and configuration are same and we have to modify only a prepare part. 

Here you have to modify a `paymentName` value. Set it to `stripe_js` or any other you want.
The rest remain the same as described basic [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) documentation.

```php
<?php
// prepare.php

include 'config.php';

$paymentName = 'stripe_js';

$storage = $payum->getStorage($detailsClass);

$details = $storage->createNew();
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

$captureToken = $tokenFactory->createCaptureToken($paymentName, $details, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

## Next

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).