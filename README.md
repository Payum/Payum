Omnipay Bridge [![Build Status](https://travis-ci.org/Payum/Payum.png?branch=master)](https://travis-ci.org/Payum/OmnipayBridge)
==============

[Omnipay](https://github.com/adrianmacneil/omnipay) created by [Adrian Macneil](http://adrianmacneil.com/). The lib provides unified api for 25+ payment gateways. Plus, it simple, has unified, consistent API and fully covered with tests.  
This bridge allows you to use omnipay gateways but in payum like way.

## How to capture?

```php
<?php
use Omnipay\Common\GatewayFactory;

use Payum\Request\CaptureRequest;
use Payum\OmnipayBridge\PaymentFactory;

$payment = PaymentFactory::create(GatewayFactory::create('Dummy'));

$payment->execute($captureRequest = new CaptureRequest(array(
    'amount' => 10,
    'card' => array(
        'number' => '5555556778250000', //end zero so will be accepted
        'cvv' => 123,
        'expiryMonth' => 6,
        'expiryYear' => 16,
        'firstName' => 'foo',
        'lastName' => 'bar',
    )
)));
```

## Was the payment finished successfully?

```php
<?php

//...
use Payum\Request\BinaryMaskStatusRequest;

$statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
$payment->execute($statusRequest);
if ($statusRequest->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

## Like it? Spread the world!

You can star the lib on [github](https://github.com/Payum/OmnipayBridge) or [packagist](https://packagist.org/packages/Payum/OmnipayBridge). You may also drop a message on Twitter.  

## Need support?

If you are having general issues with [omnipay gateway](https://github.com/Payum/OmnipayBridge) or [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the GitHub issue tracker: [omnipay gateway](https://github.com/Payum/OmnipayBridge/issues) or [payum](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

## License

OmnipayBridge is released under the MIT License. For more information, see [License](LICENSE).