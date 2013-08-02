Payex [![Build Status](https://travis-ci.org/Payum/Payex.png?branch=master)](https://travis-ci.org/Payum/Payex)
=====

The lib implements [Payum](http://www.payexpim.com/) payment.

## How to capture?

```php
<?php
use Buzz\Client\Curl;

use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;
use Payum\Payex\PaymentFactory;
use Payum\Request\CaptureRequest;

$payment = PaymentFactory::create(new OrderApi(new SoapClientFactory(), array(
   'accountNumber' => 'foo',
   'encryptionKey' => 'bar',
   'sandbox' => true
)));

$payment->execute($captureRequest = new CaptureRequest(array(
    'price' => 10000 //10 EUR
    'currency' => 'EUR',
    'vat' => 0,
    'orderId' => 123,
    'productNumber' => 123,
    'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
    'view' => OrderApi::VIEW_CREDITCARD,
    'description' => 'a desc',
    'clientIPAddress' => '127.0.0.1',
    'clientLanguage' => 'en-US'
    'clientIdentifier' => '',
    'additionalValues' => '',
    'priceArgList' => '',
    'agreementRef' => '',
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

## Sandbox

There is a sandbox ([source](https://github.com/Payum/PayumBundleSandbox) or [web](http://sandbox.payum.forma-dev.com/)) where you can find more examples of how to use the lib, store details, integrate it with symfony framework and so on.

## Like it? Spread the world!

You can star the lib on [github](https://github.com/Payum/Payex) or [packagist](https://packagist.org/packages/payum/payex). You may also drop a message on Twitter.

## Need support?

If you are having general issues with [payex](https://github.com/Payum/Payex) or [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the GitHub issue tracker: [payex](https://github.com/Payum/Payex/issues) or [payum](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

## License

Payex is released under the MIT License. For more information, see [License](LICENSE).