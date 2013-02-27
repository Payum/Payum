Payum [![Build Status](https://travis-ci.org/Payum/Payum.png?branch=master)](https://travis-ci.org/Payum/Payum)
=====

The payment lib. 

### Why would you want use it?

* When you need high level of flexibility.
* When you need domain models friendly payment solution.
* When you want to have control over every part of it.
* When you need different levels of abstraction.
* When you need good status managing.

### Which payments currently supported?

* [Paypal Express Checkout Nvp](https://github.com/Payum/PaypalExpressCheckoutNvp)
* [Paypal Pro Checkout Nvp](https://github.com/Payum/PaypalProCheckoutNvp)
* [Authorize.Net AIM](https://github.com/Payum/AuthorizeNetAim)
* [Be2Bill](https://github.com/Payum/Be2Bill)
* All gateways supported by [omnipay](https://github.com/adrianmacneil/omnipay) lib via [bridge](https://github.com/Payum/OmnipayBridge).

Also there is a [bundle](https://github.com/Payum/PayumBundle) for symfony2 developers.

### How to capture?

```php
<?php
//Source: Payum\Examples\ReadmeTest::bigPicture()

//use Payum\Examples\Action\CaptureAction;
//use Payum\Examples\Action\StatusAction;
//use Payum\Request\CaptureRequest;
//use Payum\Payment;

//Populate payment with actions.
$payment = new Payment;
$payment->addAction(new CaptureAction());

//Create request and model. It could be anything supported by an action.
$captureRequest = new CaptureRequest(array(
    'amount' => 10,
    'currency' => 'EUR'
));

//Execute request
$payment->execute($captureRequest);

echo 'We are done!';
```

### How to manage authorize redirect?

```php
<?php
//Source: Payum\Examples\ReadmeTest::interactiveRequests()

//use Payum\Examples\Request\AuthorizeRequest;
//use Payum\Examples\Action\AuthorizeAction;
//use Payum\Request\CaptureRequest;
//use Payum\Request\RedirectUrlInteractiveRequest;
//use Payum\Payment;

$payment = new Payment;
$payment->addAction(new AuthorizeAction());

$request = new AuthorizeRequest($model);

if ($interactiveRequest = $payment->execute($request, $catchInteractive = true)) {    
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        echo 'User must be redirected to '.$interactiveRequest->getUrl();
    }

    throw $interactiveRequest;
}
```

### How to check payment status

```php
<?php
//Source: Payum\Examples\ReadmeTest::gettingRequestStatus()

//use Payum\Examples\Action\StatusAction;
//use Payum\Request\BinaryMaskStatusRequest;
//use Payum\Payment;

//Populate payment with actions.
$payment = new Payment;
$payment->addAction(new StatusAction());

$statusRequest = new BinaryMaskStatusRequest($model);
$payment->execute($statusRequest);

//Or there is a status which require our attention.
if ($statusRequest->isSuccess()) {
    echo 'We are done!';
} 

echo 'Uhh something wrong. Check other possible statuses!';
```

### Like it? Spread the world!

You can star the lib on [github](https://github.com/Payum/Payum) or [packagist](https://packagist.org/packages/payum/payum). You may also drop a message on Twitter.  

### Need support?

If you are having general issues with [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

### License

Payum is released under the MIT License. For more information, see [License](LICENSE).